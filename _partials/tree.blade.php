
@foreach ($structure as $key => $value)

    <div class="item">

        @if (is_numeric($key))

            <?php
                $extension = pathinfo($value, PATHINFO_EXTENSION);
                $mapping = [
                    'php'  => 'code file outline',
                    'png'  => 'image file outline',
                    'css'  => 'css3',
                    'html' => 'html5',
                ];
            ?>

            <i class="{{ array_get($mapping, $extension, 'file outline') }} icon"></i>
            <div class="content">
                {{ $value }}
            </div>

        @elseif (is_array($value) && count($value))

            <i class="open folder icon"></i>
            <div class="content">
                <div class="header">{{ $key }}</div>
                <div class="list">
                    @include('_partials.tree', ['structure' => $value])
                </div>
            </div>

        @else

            <i class="folder outline icon"></i>
            <div class="content">
                {{ $key }}
            </div>

        @endif

    </div>

@endforeach
