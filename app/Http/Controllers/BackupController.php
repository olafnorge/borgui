<?php

namespace App\Http\Controllers;

use App\Backup;
use App\Cache\LockableTrait;
use App\Exceptions\BackupBrowseCacheNotReadyException;
use App\Jobs\ProcessBrowseSync;
use Auth;
use Cache;
use Cookie;
use File;
use Html;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Log;
use olafnorge\borgphp\ExportTarCommand;
use RuntimeException;
use Str;
use Throwable;

class BackupController extends Controller {

    use LockableTrait;


    /**
     * BackupController constructor.
     */
    public function __construct() {
        $this->authorizeResource(Backup::class, 'backup');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        // make sure we only deliver backups that belong to the current user
        $repositoyIds = Auth::user()->repositories()->pluck('id');
        $backups = Backup::whereIn('repository_id', $repositoyIds)
            ->with('repository')
            ->orderByDesc('start');

        // limit set by repository if requested
        if (request()->query('repository_id')) {
            abort_unless($repositoyIds->flip()->has(request()->query('repository_id')), 403);
            $backups->where('repository_id', request()->query('repository_id'));
        }

        return view('backup.index', [
            'backups' => $backups->paginate(5)->appends(array_only(request()->query(), ['repository_id'])),
        ]);
    }


    /**
     * @param Request $request
     * @param Backup $backup
     * @return \Illuminate\Http\JsonResponse
     */
    public function poll(Request $request, Backup $backup) {
        abort_unless($request->expectsJson() && $request->isJson(), 404);
        abort_unless($backup->repository->user_id === Auth::id(), 403);
        $folder = $request->json('folder');
        abort_unless(!empty(array_filter($backup->paths, function ($item) use ($folder) {
            return ltrim($folder, '/') ? starts_with($folder, $item) : true;
        })), 404);

        try {
            $this->getListing($backup, sha1($folder), $folder, false);

            return response()->json(['location' => route('backup.show', ['backup' => $backup, 'folder' => $folder])], 200);
        } catch (BackupBrowseCacheNotReadyException $exception) {
            return response()->json(
                ['message' => $exception->getMessage()],
                $exception->getStatusCode(),
                $exception->getHeaders()
            );
        } catch (Throwable $exception) {
            session()->flash('error', 'An unknown error has occurred. Please try again.');
            report($exception);

            return response()->json([], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Backup $backup
     * @return \Illuminate\Http\Response
     */
    public function show(Backup $backup) {
        $folder = request()->query('folder');
        abort_unless(!empty(array_filter($backup->paths, function ($item) use ($folder) {
            return ltrim($folder, '/') ? starts_with($folder, $item) : true;
        })), 404);
        $isRoot = in_array($folder, $backup->paths, true);

        try {
            return view('backup.show', [
                'backup' => $backup,
                'listings' => $this->getListing($backup, sha1($folder), $folder),
                'folder' => ltrim($folder, '/') ? $folder : null,
                'parent' => $isRoot ? null : dirname($folder),
                'breadcrumb' => $this->getBreadcrumb($backup, $isRoot, $folder),
            ]);
        } catch (BackupBrowseCacheNotReadyException $exception) {
            return view('backup.show', [
                'backup' => $backup,
                'listings' => [],
                'folder' => ltrim($folder, '/') ? $folder : null,
                'parent' => $isRoot ? null : dirname($folder),
                'breadcrumb' => $this->getBreadcrumb($backup, $isRoot, $folder),
                'retryAfter' => $exception->getRetryAfter(),
                'message' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            session()->flash('error', 'An unknown error has occurred. Please try again.');
            report($exception);

            return view('backup.show', [
                'backup' => $backup,
                'listings' => [],
                'folder' => ltrim($folder, '/') ? $folder : null,
                'parent' => $isRoot ? null : dirname($folder),
                'breadcrumb' => $this->getBreadcrumb($backup, $isRoot, $folder),
            ]);
        }
    }


    /**
     * @param Backup $backup
     * @return \Illuminate\Http\RedirectResponse
     */
    public function flush(Backup $backup) {
        abort_unless($backup->repository->user_id === Auth::id(), 403);
        abort_unless(Cache::tags($backup->getCacheTags())->flush(), 500);

        return redirect()->back()->with('success', 'Browse Cache flushed.');
    }


    /**
     * @param Request $request
     * @param Backup $backup
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request, Backup $backup) {
        abort_unless($backup->repository->user_id === Auth::id(), 403);
        $folder = $request->input('folder');
        abort_unless(!empty(array_filter($backup->paths, function ($item) use ($folder) {
            return ltrim($folder, '/') ? starts_with($folder, $item) : true;
        })), 404);

        // set cookie in order to display download spinner
        Cookie::queue(Cookie::make(
            'download_token',
            $request->input('download_token'),
            1,
            null,
            null,
            $request->isSecure(),
            false
        ));

        return response()->streamDownload(function () use ($backup, $folder) {
            $lockKey = sprintf('%s::%s', class_basename($backup->repository), $backup->repository_id);

            return $this->lock($lockKey, config('borg.lock_ttl'))->get(function () use ($backup, $folder) {
                $borgIdRsaPath = sprintf('%s/%s', config('borg.storage_path'), str_random());
                $bastionIdRsaPath = sprintf('%s/%s', config('borg.storage_path'), str_random());

                try {
                    $arguments = array_filter([
                        sprintf('%s::%s', $backup->repository->repository, $backup->name),
                        '-',
                        ltrim($folder, '/'),
                        config('borg.log_level'),
                        '--strip-components', value(function () use ($folder): int {
                            $count = mb_substr_count($folder, '/') - 1;

                            return $count > 0 ? $count : 0;
                        }),
                        '--tar-filter', 'gzip',
                        '--rsh', value(function () use ($backup, $borgIdRsaPath, $bastionIdRsaPath): string {
                            $rsh = $backup->repository->rsh;

                            if (Str::contains($rsh, '{% borg_id_rsa %}')) {
                                if (!File::put($borgIdRsaPath, $backup->repository->borg_id_rsa, true) || !File::chmod($borgIdRsaPath, 0600)) {
                                    throw new RuntimeException('Can not write private borg key');
                                }

                                $rsh = Str::replaceFirst('{% borg_id_rsa %}', $borgIdRsaPath, $rsh);
                            }

                            if (Str::contains($rsh, '{% bastion_id_rsa %}')) {
                                if (!File::put($bastionIdRsaPath, $backup->repository->bastion_id_rsa, true) || !File::chmod($bastionIdRsaPath, 0600)) {
                                    throw new RuntimeException('Can not write private bastion key');
                                }

                                $rsh = Str::replaceFirst('{% bastion_id_rsa %}', $bastionIdRsaPath, $rsh);
                            }

                            return $rsh;
                        }),
                    ]);
                    with(new ExportTarCommand(
                        $arguments,
                        config('borg.storage_path'),
                        ['BORG_PASSPHRASE' => $backup->repository->password],
                        null,
                        0
                    ))->run(function ($type, $buffer) {
                        if (ExportTarCommand::ERR === $type) {
                            Log::error('extract tar', array_map(function ($item) {
                                return json_decode($item, true) ?: $item;
                            }, array_values(array_filter(explode(PHP_EOL, $buffer)))));

                            return redirect()->back()->with('error', 'An unknown error has occurred. Please try again.');
                        } else {
                            echo $buffer;
                        }
                    });
                } catch (Throwable $exception) {
                    report($exception);

                    return redirect()->back()->with('error', 'An unknown error has occurred. Please try again.');
                } finally {
                    File::exists($borgIdRsaPath) && File::delete($borgIdRsaPath);
                    File::exists($bastionIdRsaPath) && File::delete($bastionIdRsaPath);
                }
            });
        }, sprintf('%s-%s.tar.gz', Str::slug($backup->name), ltrim(basename($folder), '.')), [
            'Content-Type' => 'application/x-gzip; charset=binary',
        ]);
    }


    /**
     * @param Backup $backup
     * @param bool $isRoot
     * @param string|null $folder
     * @return string
     */
    private function getBreadcrumb(Backup $backup, bool $isRoot, ?string $folder = null): string {
        $root = array_first(array_filter($backup->paths, function ($path) use ($folder) {
            return starts_with($folder, $path);
        }));
        $parts = array_filter(array_map('trim', explode('/', str_replace_first($root, '', $folder))));
        $roots = array_filter(array_map('trim', explode('/', $root)));

        if ($parts) {
            $breadcrumb = [Html::tag('li', array_pop($parts), ['class' => 'breadcrumb-item'])];

            while ($parts) {
                $name = array_pop($parts);
                $parent = sprintf('/%s', implode('/', array_merge($roots, $parts, [$name])));
                $breadcrumb[] = Html::tag(
                    'li',
                    Html::tag(
                        'a',
                        $name,
                        ['href' => route('backup.show', ['backup' => $backup, 'folder' => $parent])]
                    )->toHtml(),
                    ['class' => 'breadcrumb-item']
                );
            };
        }

        if ($roots) {
            if (!$isRoot) {
                $name = array_pop($roots);
                $parent = sprintf('/%s', implode('/', array_merge($roots, [$name])));
                $breadcrumb[] = Html::tag(
                    'li',
                    Html::tag(
                        'a',
                        $name,
                        ['href' => route('backup.show', ['backup' => $backup, 'folder' => $parent])]
                    )->toHtml(),
                    ['class' => 'breadcrumb-item']
                );
            }

            while ($roots) {
                $breadcrumb[] = Html::tag('li', array_pop($roots), ['class' => 'breadcrumb-item text-muted']);
            };
        }

        $breadcrumb[] = Html::tag('li', '', ['class' => 'breadcrumb-item']);

        return new HtmlString(sprintf('<ol class="breadcrumb border-0 m-0 pt-0 pl-0">%s</ol>', implode('', array_reverse($breadcrumb))));
    }


    /**
     * @param Backup $backup
     * @param string $cacheKey
     * @param string|null $folder
     * @param bool $fetch
     * @return array
     */
    private function getListing(Backup $backup, string $cacheKey, ?string $folder = null, bool $fetch = true): array {
        // no folder, no action
        if (!ltrim($folder, '/')) {
            return [];
        }

        if (Cache::tags($backup->getCacheTags())->has($cacheKey)) {
            return $fetch ? decrypt(Cache::tags($backup->getCacheTags())->get($cacheKey)) : [];
        } else {
            ProcessBrowseSync::dispatch($backup, $cacheKey, $folder);
        }

        throw new BackupBrowseCacheNotReadyException(sprintf(
            'The requested folder %s is not ready yet. We will retry loading the contents every %s seconds. You will be redirected automatically. Please stay tuned.',
            $folder,
            5
        ), 5);
    }
}
