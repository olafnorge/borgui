<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepository;
use App\Http\Requests\UpdateRepository;
use App\Repository;
use Auth;

class RepositoryController extends Controller {


    /**
     * RepositoryController constructor.
     */
    public function __construct() {
        $this->authorizeResource(Repository::class, 'repository');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $repositories = Auth::user()
            ->repositories()
            ->orderBy('name', 'asc')
            ->paginate();

        return view('repository.index', ['repositories' => $repositories]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('repository.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRepository $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreRepository $request) {
        return Repository::create($request->validated())
            ? redirect()->route('repository.index')->with('success', sprintf('Repository %s created.', $request->input('name')))
            : redirect()->route('repository.create')->with('error', 'The data could not be saved. An error occurred.')->withInput();
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Repository $repository
     * @return \Illuminate\Http\Response
     */
    public function edit(Repository $repository) {
        return view('repository.edit', ['repository' => $repository]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRepository $request
     * @param \App\Repository $repository
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRepository $request, Repository $repository) {
        return $repository->fill($request->validated())->save()
            ? redirect()->route('repository.edit', ['repository' => $repository])->with('success', sprintf('Repository %s updated.', $request->input('name')))
            : redirect()->route('repository.edit', ['repository' => $repository])->with('error', 'The data could not be saved. An error occurred.')->withInput();
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Repository $repository
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Repository $repository) {
        return $repository->delete()
            ? redirect()->route('repository.index')->with('success', sprintf('Repository %s has been deleted.', $repository->name))
            : redirect()->route('repository.index')->with('error', sprintf('Deleting Repository %s failed. Please try again.', $repository->name));
    }
}
