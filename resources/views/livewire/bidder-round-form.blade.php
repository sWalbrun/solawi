<?php

/**
 * @var BidderRound $bidderRound
 */

use App\Models\BidderRound;

?>

<div class="w-3/4 grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="box-border p-4 border-4 mb-5 mt-5 relative">
        <h1 class="mb-5">
            {{ $bidderRound->exists ? $bidderRound->__toString() : trans('Neue Bieterrunde anlegen') }}
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="w-3/4 opacity-60">
                <label for="validFrom">{{trans('Begin des Jahres')}}</label>
                <input class="form-control rounded-md"
                       wire:model="validFrom"
                       type="text" class="form-control datepicker" placeholder="{{\Carbon\Carbon::now()->startOfYear()->format('d.m.Y')}}"
                       autocomplete="off"
                       data-provide="datepicker" data-date-autoclose="true"
                       data-date-format="dd.mm.yyyy" data-date-today-highlight="true"
                       onchange="this.dispatchEvent(new InputEvent('input'))"
                       id="validFrom"
                />
            </div>

            <div class="w-3/4 opacity-60">
                <label for="validTo">{{trans('Ende des Jahres')}}</label>
                <input class="form-control rounded-md"
                       wire:model="validTo"
                       type="text" class="form-control datepicker" placeholder="{{\Carbon\Carbon::now()->endOfYear()->format('d.m.Y')}}"
                       autocomplete="off"
                       data-provide="datepicker" data-date-autoclose="true"
                       data-date-format="dd.mm.yyyy" data-date-today-highlight="true"
                       onchange="this.dispatchEvent(new InputEvent('input'))"
                       id="validTo"
                />
            </div>

            <div class="w-3/4 opacity-60">
                <label for="startOfSubmission">{{trans('Begin der Abstimmung')}}</label>
                <input class="form-control rounded-md"
                       wire:model="startOfSubmission"
                       type="text" class="form-control datepicker" placeholder="{{\Carbon\Carbon::now()->format('d.m.Y')}}"
                       autocomplete="off"
                       data-provide="datepicker" data-date-autoclose="true"
                       data-date-format="dd.mm.yyyy" data-date-today-highlight="true"
                       onchange="this.dispatchEvent(new InputEvent('input'))"
                       id="startOfSubmission"
                />
            </div>

            <div class="w-3/4 opacity-60">
                <label for="endOfSubmission">{{trans('Ende der Abstimmung')}}</label>
                <input class="form-control rounded-md"
                       wire:model="endOfSubmission"
                       type="text" class="form-control datepicker"
                       placeholder="{{\Carbon\Carbon::now()->addMonth()->endOfMonth()->format('d.m.Y')}}"
                       autocomplete="off"
                       data-provide="datepicker" data-date-autoclose="true"
                       data-date-format="dd.mm.yyyy" data-date-today-highlight="true"
                       onchange="this.dispatchEvent(new InputEvent('input'))"
                       id="endOfSubmission"
                />
            </div>

            <div class="w-3/4">
                <x-inputs.maskable
                    mask="##.###"
                    label="{{trans('Zu erreichender Betrag')}}"
                    placeholder="{{__('Betrag')}}"
                    wire:model.defer="bidderRound.targetAmount"
                    suffix="€"
                />
            </div>

            <div class="w-3/4">
                <x-input
                    label="{{trans('Anzahl der Runden')}}"
                    placeholder="{{__('Runden')}}"
                    wire:model="bidderRound.countOffers"
                />
            </div>

            @if(isset($bidderRound->bidderRoundReport) && $bidderRound->bidderRoundReport->roundWon)
                <span class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-green-400 text-white">
            {{trans("Der Zielbetrag wurde mit der Runde {$bidderRound->bidderRoundReport->roundWon} erreicht")}}
                </span>
            @elseif($bidderRound->exists && $bidderRound->bidderRoundBetweenNow())
                <span wire:click="calculateBidderRound()"
                      class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-yellow-300 text-gray-800">
                {{trans('Die Bieterrunde läuft gerade (Klicken um Prüfung durchzuführen)')}}
        </span>
            @elseif($bidderRound->exists && $bidderRound->endOfSubmission->lt(\Carbon\Carbon::now()))
                <span class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-gray-800">
                {{trans('Die Bieterrunde wurde bereits abgeschlossen')}}
        </span>
            @elseif($bidderRound->exists)
                <span class="inline-flex items-center mt-3 px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-gray-800">
                {{trans('Die Bieterrunde hat noch nicht begonnen')}}
        </span>
            @endif

            <div class="relative py-2 pr-2">
                <x-button rounded positive label="{{__('Speichern')}}" wire:click="save()"/>
            </div>
        </div>

        <x-dialog/>
        <x-errors/>
    </div>

    @if($bidderRound->bidderRoundReport()->exists())
        <div class="box-border p-4 border-4 mb-5 mt-5">
            @livewire('bidder-round-report-form', ['bidderRoundReport' => $bidderRound->bidderRoundReport])
        </div>
    @endif
</div>

<div class="box-border w-3/4 p-4 border-4">
    <div class="mt-10 bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <livewire:bidding-round-overview-table bidderRoundId="{{$bidderRound->exists ? $bidderRound->id : 0}}"/>
    </div>
</div>
