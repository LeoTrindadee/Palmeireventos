<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\TelegramService;

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

    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function store(Request $request)
    {
        $event = new Event;

        // Preencher os campos do evento
        $event->title = $request->title; 
        $event->date = $request->date;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items;

        // Upload da imagem
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $requestImage = $request->image;
            $extension = $requestImage->extension();
            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;
            $requestImage->move(public_path('img/events'), $imageName);
            $event->image = $imageName;
        }

        // Obter o usuário autenticado
        $user = auth()->user();
        $event->user_id = $user->id;

        // Salvar o evento no banco de dados
        $event->save();

        // Enviar notificação para o Telegram
        $chatId = '5144099939'; // Substitua pelo chat ID real do usuário
        $message = "Um novo evento foi criado:\nTítulo: {$event->title}\nData: {$event->date}\nCidade: {$event->city}\nDescrição: {$event->description}";

        $this->telegramService->sendMessage($chatId, $message); // Envia a mensagem

        // Redirecionar com uma mensagem de sucesso
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
