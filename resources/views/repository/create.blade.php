@extends('layouts.app')

@section('content')
    {{ Form::open(['route' => 'repository.store', 'method' => 'POST']) }}
        {{ Form::text('name', null, ['placeholder' => 'Name of the Repository', 'required']) }}
        {{ Form::text('repository', null, ['placeholder' => 'Location of the Repository', 'required']) }}
        {{ Form::password('password', ['placeholder' => 'Password of the Repository', 'required']) }}
        {{ Form::text('rsh', null, [
            'placeholder' => 'RSH for the Repository (f.e. \'ssh -l username\')',
            'label' => 'RSH',
            'help' => 'Placeholders: {% borg_id_rsa %} - use private key for borg host authentication; {% bastion_id_rsa %} - use private key for bastion host authentication',
        ]) }}
        {{ Form::textarea('borg_id_rsa', null, ['placeholder' => 'Private Key for authentication against the borg host', 'label' => 'Private Borg-Host Key']) }}
        {{ Form::textarea('bastion_id_rsa', null, ['placeholder' => 'Private Key for authentication against a bastion host', 'label' => 'Private Bastion-Host Key']) }}
    {{ Form::close([ Form::submit(__('Save')) ]) }}
@endsection
