@php
    /**
     * Guanzon Beach map — clean interactive layout.
     * Suite = horizontal row at bottom-left.
     * Entrance = center corridor path from the top.
     */
    $mapId = $mapId ?? 'rpResortMap';
    $mapCompact = (bool) ($mapCompact ?? false);

    $roomBtn = function (int $n, string $zone, string $shape = 'cabin') {
        $aria = match ($zone) {
            'suite' => "Suite room {$n}",
            'ac' => "AC room {$n}",
            'cabana' => "Cabana {$n}",
            'beach' => "Beach house {$n}",
            'villa' => "Villa {$n}",
            'payag' => "Payag {$n}",
            'cottage' => "Cottage {$n}",
            default => "Room {$n}",
        };
        echo '<button type="button" class="rp-map-room rp-map-room--'.$shape.'" data-map-zone="'.$zone.'" data-map-label="'.$n.'" aria-label="'.$aria.'">';
        echo '<span class="rp-map-room-icon" aria-hidden="true"></span>';
        echo '<span class="rp-map-room-tag">RM '.$n.'</span>';
        echo '</button>';
    };
@endphp

<section
    class="rp-resort-map {{ $mapCompact ? 'rp-resort-map--compact' : '' }}"
    id="{{ $mapId }}"
    data-rp-resort-map
    aria-label="Guanzon Beach resort map"
>
    <div class="rp-resort-map-toolbar">
        <div>
            <p class="rp-resort-map-kicker">Guanzon Beach</p>
            <h3 class="rp-resort-map-title">Resort map</h3>
            <p class="rp-resort-map-hint" data-rp-map-hint>Select a numbered room for this accommodation type.</p>
        </div>
        <div class="rp-resort-map-toolbar-side">
            <div class="rp-resort-map-zoom-bar" data-rp-map-zoom-bar>
                <button type="button" class="rp-map-zoom-btn" data-rp-map-zoom-out aria-label="Zoom out">
                    <i class="bi bi-dash-lg" aria-hidden="true"></i>
                </button>
                <span class="rp-map-zoom-label" data-rp-map-zoom-label>100%</span>
                <button type="button" class="rp-map-zoom-btn" data-rp-map-zoom-in aria-label="Zoom in">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                </button>
                <button type="button" class="rp-map-zoom-btn rp-map-zoom-btn--reset" data-rp-map-zoom-reset aria-label="Reset zoom">
                    Reset
                </button>
            </div>
            <div class="rp-resort-map-legend" aria-hidden="true">
                <span><i class="rp-map-legend-dot is-selectable"></i> Selectable</span>
                <span><i class="rp-map-legend-dot is-selected"></i> Selected</span>
                <span><i class="rp-map-legend-dot is-other"></i> Other areas</span>
            </div>
        </div>
    </div>

    <div class="rp-resort-map-canvas" data-rp-map-canvas>
        <p class="rp-resort-map-pan-hint"><i class="bi bi-arrows-move" aria-hidden="true"></i> Drag to pan · Ctrl+scroll or pinch to zoom</p>

        <div class="rp-map-zoom-space" data-rp-map-zoom-space>
            <div class="rp-map-board" data-rp-map-board>
            <div class="rp-map-sketch">
                <div class="rp-map-sketch-ac-left">
                    <div class="rp-map-zone" data-map-zone="ac">
                        <div class="rp-map-zone-label">Room with AC</div>
                        <div class="rp-map-rooms rp-map-rooms--col">
                            @foreach(range(12, 1) as $n)
                                @php $roomBtn($n, 'ac', 'cabin'); @endphp
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="rp-map-sketch-entrance" aria-label="Entrance">
                    <div class="rp-map-entrance-label">Entrance</div>
                    <div class="rp-map-entrance-path"></div>
                </div>

                <div class="rp-map-sketch-main">
                    <div class="rp-map-zone rp-map-zone--ac-top" data-map-zone="ac">
                        <div class="rp-map-zone-label">Room with AC</div>
                        <div class="rp-map-rooms rp-map-rooms--row rp-map-rooms--nowrap">
                            @foreach(range(13, 21) as $n)
                                @php $roomBtn($n, 'ac', 'cabin'); @endphp
                            @endforeach
                        </div>
                    </div>

                    <div class="rp-map-sketch-main-mid">
                        <div class="rp-map-zone" data-map-zone="cabana">
                            <div class="rp-map-zone-label">Cabana</div>
                            <div class="rp-map-rooms rp-map-rooms--row rp-map-rooms--nowrap">
                                @foreach(range(1, 10) as $n)
                                    @php $roomBtn($n, 'cabana', 'cabin'); @endphp
                                @endforeach
                            </div>
                            <div class="rp-map-rooms rp-map-rooms--row rp-map-rooms--nowrap">
                                @foreach(range(11, 20) as $n)
                                    @php $roomBtn($n, 'cabana', 'cabin'); @endphp
                                @endforeach
                            </div>
                        </div>

                        <div class="rp-map-zone" data-map-zone="ac">
                            <div class="rp-map-zone-label">Room with AC</div>
                            <div class="rp-map-rooms rp-map-rooms--col">
                                @foreach(range(22, 30) as $n)
                                    @php $roomBtn($n, 'ac', 'cabin'); @endphp
                                @endforeach
                            </div>
                        </div>

                        <div class="rp-map-zone rp-map-zone--ac-mid-right" data-map-zone="ac">
                            <div class="rp-map-zone-label">Room with AC</div>
                            <div class="rp-map-ac-right">
                                <div class="rp-map-rooms rp-map-rooms--col rp-map-ac-right-side">
                                    @foreach([31, 32] as $n)
                                        @php $roomBtn($n, 'ac', 'cabin'); @endphp
                                    @endforeach
                                </div>
                                <div class="rp-map-rooms rp-map-rooms--row rp-map-rooms--nowrap rp-map-ac-right-row">
                                    @foreach(range(33, 44) as $n)
                                        @php $roomBtn($n, 'ac', 'cabin'); @endphp
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rp-map-sketch-main-bottom">
                        <div class="rp-map-zone" data-map-zone="beach">
                            <div class="rp-map-zone-label">Beach House</div>
                            <div class="rp-map-rooms rp-map-rooms--row rp-map-rooms--nowrap">
                                @foreach(range(1, 2) as $n)
                                    @php $roomBtn($n, 'beach', 'house'); @endphp
                                @endforeach
                            </div>
                        </div>
                        <div class="rp-map-zone" data-map-zone="villa">
                            <div class="rp-map-zone-label">Villa</div>
                            <div class="rp-map-rooms rp-map-rooms--grid2">
                                @foreach(range(1, 4) as $n)
                                    @php $roomBtn($n, 'villa', 'house'); @endphp
                                @endforeach
                            </div>
                        </div>
                        <div class="rp-map-zone" data-map-zone="payag">
                            <div class="rp-map-zone-label">Payag</div>
                            <div class="rp-map-payag">
                                <div class="rp-map-rooms rp-map-rooms--col rp-map-rooms--payag">
                                    @foreach(range(1, 7) as $n)
                                        @php $roomBtn($n, 'payag', 'hut'); @endphp
                                    @endforeach
                                </div>
                                <div class="rp-map-rooms rp-map-rooms--col rp-map-rooms--payag">
                                    @foreach(range(8, 14) as $n)
                                        @php $roomBtn($n, 'payag', 'hut'); @endphp
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="rp-map-zone" data-map-zone="cottage">
                            <div class="rp-map-zone-label">Cottage</div>
                            @foreach([range(1, 7), range(8, 14), range(15, 21), range(22, 28)] as $row)
                                <div class="rp-map-rooms rp-map-rooms--row rp-map-rooms--nowrap">
                                    @foreach($row as $n)
                                        @php $roomBtn($n, 'cottage', 'cabin'); @endphp
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="rp-map-sketch-suite">
                    <div class="rp-map-zone" data-map-zone="suite">
                        <div class="rp-map-zone-label">Suite Room</div>
                        <div class="rp-map-rooms rp-map-rooms--row rp-map-rooms--nowrap rp-map-rooms--suite">
                            {{-- RM 1 is nearest the entrance (right side), then 2–5 going left. --}}
                            @foreach(range(5, 1) as $n)
                                @php $roomBtn($n, 'suite', 'cabin'); @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="rp-map-scroll-pad" aria-hidden="true"></div>
            </div>
        </div>
    </div>
</section>
