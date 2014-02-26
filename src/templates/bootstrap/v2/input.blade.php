<!-- Boostratp V2 -->

@if (isset($label)) 
	<label class="label">{{$label}}</label>
@endif

<label class="input">
	@if ($icon)
		<i class="icon-{{isset($iconAppend) ? 'append' : 'prepend'}} {{$icon}}"></i>
	@endif
	<input {{$__attributes}}>
</label>

@if ($note)
	<div class="note note-error">{{$note}}</div>
@endif
