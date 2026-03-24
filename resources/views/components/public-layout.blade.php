@props(['heading' => null])

@include('layouts.public', [
    'slot' => $slot,
    'citizenAreaHeading' => $heading,
])
