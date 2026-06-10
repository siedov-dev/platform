@foreach($key_types as $key_type)
    @include('desktop.slc.treasures.chest-card', ['ket_type' => $key_type, 'inventory' => $inventory])
@endforeach
