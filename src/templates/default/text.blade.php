@if(@_name->has)
	@input(text,name=@_1,@_PARAMETERS)
@else
	@input(text,@_PARAMETERS)
@endif