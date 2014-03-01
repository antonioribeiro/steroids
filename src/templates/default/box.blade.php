	<div class="row">

		<!-- NEW COL START -->
		<article class="col-sm-12 col-md-12 col-lg-12">
			
			<!-- Widget ID (each widget will need unique ID)-->
				@include('views.site._widgets.openJarvisWidget')				

				<!-- widget div-->
				<div>
				
					<!-- widget edit box -->
					<div class="jarviswidget-editbox">
						<!-- This area used as dropdown edit box -->
						
					</div>
					<!-- end widget edit box -->
					

					<div class="widget-body no-padding">
						<!-- Error states for elements -->
						@_BODY
					</div>
					<!-- end widget content -->
					
				</div>
				<!-- end widget div -->
				
			</div>
			<!-- end widget -->
			
		</article>
		<!-- END COL -->

	</div>

	<!-- END ROW -->