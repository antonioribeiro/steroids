@php
  $options = array('url' => '@_url', 'method' => (@_method->has ? '@_method' : 'POST') )
@@

{{ Form::model(@_model, $options)) }}
    @_BODY
{{ Form::close() }}