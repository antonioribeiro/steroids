@php
  $options = array('url' => '@_url', 'method' => ('@_method' ?: 'POST', 'class' => '@_class->bare') )
@@

{{ Form::open($options) }}
    @_BODY
{{ Form::close() }}