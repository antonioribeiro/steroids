@php
  $options = array(
  					'url' => '@_url', 
  					'method' => ('@_method' ?: 'POST'), 
  					'class' => '@_class->bare',
  					'role' => @_role->has ? '@_role' : 'default'
  				)
@@

{{ Form::open($options) }}
    @_BODY
{{ Form::close() }}