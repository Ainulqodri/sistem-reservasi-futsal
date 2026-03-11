@extends('layouts.app')

@section('header')
    <div class="mt-10 md:mt-24 mb-10">
        <h1 class="text-center text-3xl md:text-6xl text-white font-bold">
            Booking
        </h1>
    </div>
@endsection

@section('content')
    <section class="py-10 md:py-24">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-col md:flex-row gap-3 mb-10">
                <div class="w-full md:w-8/12">
                    <img src="{{ asset('storage/' . $lapangan->images[0]) }}" alt=""
                        class="rounded-xl h-42 md:h-[400px] w-full object-cover">
                </div>
                <div class="w-full md:w-4/12">
                    <div class="grid grid-cols-2 md:grid-cols-1 gap-3">
                        @if ($lapangan->images && count($lapangan->images) > 1)
                            @for ($i = 1; $i < count($lapangan->images); $i++)
                                @if ($lapangan->images[$i])
                                    <img src="{{ asset('storage/' . $lapangan->images[$i]) }}" alt=""
                                        class="rounded-xl w-full h-28 md:h-[194px] object-cover">
                                @endif
                            @endfor
                        @endif
                    </div>
                </div>
            </div>

            <livewire:booking-form :lapanganId="$lapangan->id" />
            </div>
    </section>
@endsection
