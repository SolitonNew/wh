@foreach(array_keys(config('din.mmcu_list')) as $n => $m)
#define MMCU_{{ strtoupper($m) }} {{ $n + 1 }}
@endforeach

#define MMCU MMCU_{{ strtoupper($mmcu) }}