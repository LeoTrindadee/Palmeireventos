@extends('layouts.main')

@section('title', 'Dasboard')

@section('content')

<div class="col-md-10 offset-md-1 dashboard-title-container">
    <h1>Meus Eventos</h1>
</div>
<div class="col-md-10 offset-md-1 dashboard-events-container">
    @if(count($eventsOfUser) > 0)
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nome</th>
                <th scope="col">Participantes</th>
                <th scope="col">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($eventsOfUser as $eventOfUser)
                <tr>
                    <td scropt="row">{{ $loop->index + 1 }}</td>
                    <td><a href="/events/{{ $eventOfUser->id }}">{{ $eventOfUser->title }}</a></td>
                    <td>{{ count($eventOfUser->users) }}</td>
                    <td>
                        <a href="/events/edit/{{ $eventOfUser->id }}" class="btn btn-info edit-btn"><i class="bi bi-pencil-square"></i> Editar</a>
                        <form action="/events/ {{ $eventOfUser->id }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger delete-btn"><i class="bi bi-trash-fill"></i>Deletar</button>
                        </form>
                    </td>
                </tr>
            @endforeach    
        </tbody>
    </table>
    @else
    <p>Você ainda não tem eventos, <a href="/events/create">criar evento</a></p>
    @endif
</div>
<div class="col-md-10 offset-md-1 dashboard-title-container">
    <h1>Eventos que estou participando</h1>
</div>
<div class="col-md-10 offset-md-1 dashboard-events-container">
@if(count($eventsParticipants) > 0)
<table class="table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Nome</th>
            <th scope="col">Participantes</th>
            <th scope="col">Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach($eventsParticipants as $event)
            <tr>
                <td scropt="row">{{ $loop->index + 1 }}</td>
                <td><a href="/events/{{ $event->id }}">{{ $event->title }}</a></td>
                <td>{{ count($event->users) }}</td>
                <td>
                    <form action="/events/leave/{{ $event->id }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger delete-btn"><i class="bi bi-trash-fill"></i>Sair do Evento</button>
                    </form>
                    
                </td>
            </tr>
        @endforeach    
    </tbody>
</table>
@else
<p>Você ainda não está participando de nenhum evento, <a href="/">veja todos os eventos</a></p>
@endif
</div>
@endsection