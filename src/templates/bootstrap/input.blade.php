<!-- Boostratp -->

@if (@label->has@)
	<label class="label">@label@</label>
@endif

<label class="input">
	@if (@icon->has@)
		<i class="fa fa-@_icon_@"></i>
	@endif
	<input class="form-input @class->bare@" @placeholder@>
</label>

@if (@note@)
	<div class="note note-error">@_note_@</div>
@endif
