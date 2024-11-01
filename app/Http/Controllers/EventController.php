<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Event;

use App\Models\User;

class EventController extends Controller
{
    public function index(){

        $search = request('search');

        if($search){

            $events = Event::where([
                [ 'title', 'like', '%'.$search.'%' ]
            ])->get();


        } else {
            $events = Event::all();
        }

        
        return view('welcome', [  'events' => $events, 'search'=> $search ]); //Aqui nesse Array ele pega a variável e passa para o nome entre '' (aspas simples)
                                        //E esse nome que será colocado entre chaves lá no blade para aparecer o valor da variável
    }

    public function create(){
        return view('events.create');
    }

    public function store(Request $request){
        
        $event = new Event;

        $event->title = $request->title; 
        $event->date = $request->date;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items;

        //image Upload
        if($request->hasFile('image') && $request->file('image')->isValid()){ // Verifica se a requisição possui imagem e se ela é válida

            $requestImage = $request->image; //Variável que armazena a imagem da requisição

            $extension = $requestImage->extension(); //Variável que armazena a extensão(jpg,png, etc) imagem da requisição

            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension; // hash criada a partir do nome original do arquivo
                                                                                                            // e do timestamp atual seguido pela extensão concatenando com um "."

            $requestImage->move(public_path('img/events'), $imageName); //Move a imagem para a pasta publica, especificamente na pasta events dentro da pasta img

            $event->image = $imageName; // Passando para a coluna image da tabela o nome da imagem criado na Hash
        }

        $user = auth()->user(); //Variável que vai receber o usuário autenticado
        $event->user_id = $user->id; //Coluna 'user_id' da tabela de eventos recebe o id da varivél user

        $event->save();// Salva a instância no banco de dados (Assim que o usuário criar o evento, será enviado ao banco o ID dele junto com o evento criado)

        return redirect('/')->with('msg', 'Evento criado com sucesso!');
    }

    public function show($id) {
        $events = Event::findOrFail($id); //Pega as informações do model(dados do bando de dados) e usao uma função
                                          // Que se achar o id ele dpa certo, se não a dá erro 404

        $user = auth()->user();
        $hasUserJoined = false;

            if($user){

                $userEvents = $user->eventsAsParticipants->toArray();

                foreach($userEvents as $userEvent){
                    if($userEvent['id'] == $id){
                        $hasUserJoined = true;
                    }
                }

            }

        $eventOwner = User::where('id', $events->user_id)->first()->toArray(); //Variável que recebe uma consulta na tabela de usuários onde o id do usuário
                                                                               //for do mesmo usuário da tabela de eventos(que criou o evento), ele vai pegar o primeiro  e tranformar em array

        return view('events.show', [ 'events' => $events, 'eventOwner' => $eventOwner, 'hasUserJoined'=> $hasUserJoined ]); //Aqui, por fim, vai direcionar para o diretório 'events.show'
                                                             // No qual vai estar toda a estrutura da página que o usuário quer acessar
    }

    public function dashboard () {
        $user = auth()->user(); //Variável que vai receber o usuário autenticado

        $eventsOfUser = $user->events; //Variável que vai receber a  relação feita no model de usuário (Um usuário tem muitos eventos)

        $eventsParticipants = $user->eventsAsParticipants;

        return view('events.dashboard', [ 'eventsOfUser' => $eventsOfUser, 'eventsParticipants' => $eventsParticipants ]);

    }

    public function destroy($id){

        Event::findOrFail($id)->delete();

        return redirect('/dashboard')->with('msg', 'Evento excluído com sucesso!');
    }

    public function edit($id){

        $user = auth()->user();

        $events = Event::findOrFail($id);

        if($user->id != $events->user_id){
            return redirect('/dashboard');
        }

        return view('events.edit', [ 'events' => $events ]);
    }

    public function update(Request $request){

        $data = $request->all();

        // Image Upload
        if($request->hasFile('image') && $request->file('image')->isValid()) {

            $requestImage = $request->image;

            $extension = $requestImage->extension();

            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;

            $requestImage->move(public_path('img/events'), $imageName);

            $data['image'] = $imageName;

        }

        Event::findOrFail($request->id)->update($data);

        return redirect('/dashboard')->with('msg', 'Evento editado com sucesso!');

    }

    public function joinEvent($id){

        $user = auth()->user();
        
        $user->eventsAsParticipants()->attach($id);

        $events = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Sua Presença está confirmada no evento' . $events->title);


    }

    public function leavingEvent($id){

        $user = auth()->user();

        $user->eventsAsParticipants()->detach($id);

        $events = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Sua Presença foi removida do evento' . $events->title);

    }

}
