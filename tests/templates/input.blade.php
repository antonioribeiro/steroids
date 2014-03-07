<!--DEFAULT-->
@if (@_label->has) 
	<!--label1--><label class="label">@_label</label><!--/label1-->
@endif
<label class="input">
	@if (@_icon->has)
		@php
			$__icon = @_icon_append->has && @_icon_append == true ? 'append' : 'prepend';
		@@
		<i class="icon-{{$__icon}} fa fa-user"></i>
	@endif

	<input type="@_1" @_ATTRIBUTES />
</label>