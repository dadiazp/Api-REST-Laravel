<h1>{{$tituloIndice}}</h1>
<ul>
    @foreach ($animalesIndice as $animal)
        <li>{{$animal}}</li>
    @endforeach 
</ul>