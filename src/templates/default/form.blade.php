@php
  $options = array('url' => '@_url', 'method' => (@_method->has ? '@_method' : 'POST') )
@@

{{ Form::open($options)) }}
    @_BODY
{{ Form::close() }}