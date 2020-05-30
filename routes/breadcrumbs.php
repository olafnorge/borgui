<?php

// always prepend home route
Breadcrumbs::before(function ($trail) {
    $trail->push('Home', route('repository.index'));
});

// fallback
Breadcrumbs::for('fallback', function () {
    // noop
});

// simple macro in order to group breadcrumbs
Breadcrumbs::macro('group', function (string $prefix, callable $callback) {
    $callback($prefix);
});

// breadcrumbs for the repository routes
Breadcrumbs::group('repository', function (string $prefix) {
    Breadcrumbs::for(sprintf('%s.index', $prefix), function ($trail) use ($prefix) {
        $trail->push(
            ucfirst(Str::plural($prefix)),
            route(sprintf('%s.index', $prefix)),
            ['menu' => Html::tag('a', [
                Html::tag('i', '', ['class' => 'icon-folder']),
                sprintf(' Create new %s', ucfirst($prefix))
            ], ['class' => 'btn', 'href' => route(sprintf('%s.create', $prefix))])]
        );
    });

    Breadcrumbs::for(sprintf('%s.create', $prefix), function ($trail) use ($prefix) {
        $trail->parent(sprintf('%s.index', $prefix));
        $trail->push(sprintf('Create new %s', ucfirst($prefix)), route(sprintf('%s.create', $prefix)));
    });

    Breadcrumbs::for(sprintf('%s.edit', $prefix), function ($trail, $repository) use ($prefix) {
        $trail->parent(sprintf('%s.index', $prefix));

        if (Auth::user()->can('update', $repository)) {
            $trail->push(sprintf('Edit %s - %s', ucfirst($prefix), $repository->name));
        }
    });
});

// breadcrumbs for the backup routes
Breadcrumbs::group('backup', function (string $prefix) {
    Breadcrumbs::for(sprintf('%s.index', $prefix), function ($trail) use ($prefix) {
        $trail->push(ucfirst(Str::plural($prefix)), route(sprintf('%s.index', $prefix)));
    });

    Breadcrumbs::for(sprintf('%s.show', $prefix), function ($trail, $backup) use ($prefix) {
        $trail->parent(sprintf('%s.index', $prefix));

        if (Auth::user()->can('view', $backup)) {
            $trail->push(sprintf('Show %s - %s', ucfirst($prefix), $backup->name));
        }
    });
});
