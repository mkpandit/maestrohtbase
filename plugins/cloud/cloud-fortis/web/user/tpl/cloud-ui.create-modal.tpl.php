<form id="create-vm-form" action="index.php">
<input class="hidden" name="cloud_ui" value="create" type="hidden">
<div>
<h3>Profile</h3>
	<section>
		<!--<div class="row"> -->
			<div class="col-lg-12">
				<div class="form-group">
					<label for="cloud_hostname_input">Name *</label>
					{cloud_hostname_input}
				</div>
				<div class="form-group hide">
					<input id="cloud_appliance_capabilities" name="cloud_appliance_capabilities" type="text"">
					<input id="cloud_profile_name" name="cloud_profile_name" type="text"">
				</div>
				<div class="form-group">
					<label for="cloud_virtualization_select">Type *</label>
					{cloud_virtualization_select}
				</div>
				<div class="form-group">
					<label for="cloud_image_select">Image *</label>
					{cloud_image_select}
				</div>
				<div class="form-group">
					<label for="cloud_kernel_select">Kernel *</label>
					{cloud_kernel_select}
				</div>
			</div>
			<p>(*) Mandatory</p>
		<!-- </div> -->
	</section>
<h3>Compute</h3>
	<section>
		<!-- <div class="row">  -->
			<div class="col-lg-12">
				<div class="form-group">
					<label for="cloud_disk_select">Disk *</label>
					{cloud_disk_select}
				</div>
				<div class="form-group">
					<label for="cloud_cpu_select">CPU *</label>
					{cloud_cpu_select}
				</div>
				<div class="form-group">
					<label for="cloud_memory_select">Memory *</label>
					{cloud_memory_select}
				</div>
			</div>
			<p>(*) Mandatory</p>
		<!-- </div> -->
	</section>
<h3>Network</h3>
	<section>
		<!-- <div class="row">  -->
			<div class="col-lg-12">
				<div class="form-group hide">
					<label for="cloud_network_select">Select Network *</label>
					{cloud_network_select}
				</div>
				<div class="form-group">
					<label for="cloud_ip_select_0">Network *</label>
					{cloud_ip_select_0}
				</div>
			</div>
			<p>(*) Mandatory</p>
		<!-- </div> -->
	</section>
<h3>Marketplace</h3>
	<section>
		<!-- <div class="row">  -->
			<!--
			<div class="col-sm-1">
				<i class="fa fa-arrow-left" aria-hidden="true"></i>
			</div>
			-->
			<div class="col-lg-12">
				<div class="form-group row d-inline">
					<label class="col-sm-3 col-form-label">Marketplace</label>
					<div class="col-sm-6 pull-right">
						<div class="form-group row pull-right">
							<label class="col-form-label" for="search-app">Search:&nbsp;&nbsp;</label>
							<div class="form-input-icon form-input-icon-right">
								<i class="fa fa-search" aria-hidden="true"></i>
								<input class="form-control" id="search-app" placeholder="Search" type="text">
							</div>
						</div>
					</div>
				</div>

				<div class="form-group d-inline-block" style="width: 100%;">
					<div class="owl-carousel uninitiated owl-theme">
						<div class="item">
							{cloud_application_select_0_label}
							{cloud_application_select_0_checkbox}
						</div>
						<div class="item">
							{cloud_application_select_1_label}
							{cloud_application_select_1_checkbox}
						</div>
						<div class="item">
							{cloud_application_select_2_label}
							{cloud_application_select_2_checkbox}
						</div>
						<div class="item">
							{cloud_application_select_3_label}
							{cloud_application_select_3_checkbox}
						</div>
						<div class="item">
							{cloud_application_select_4_label}
							{cloud_application_select_4_checkbox}
						</div>
						<div class="item">
							{cloud_application_select_5_label}
							{cloud_application_select_5_checkbox}
						</div>
						<div class="item">
							{cloud_application_select_6_label}
							{cloud_application_select_6_checkbox}
						</div>
						<div class="item">
							{cloud_ha_select_label}
							{cloud_ha_select_checkbox}
						</div>
					</div>
				</div>
			</div>
			
	</section>
<h3>Summary</h3>
	<section>
		<label>Review VM Settings:</label>
		<div id="summary-tab" class="col-lg-12">
			<p>
			</p>
			<div class="form-group hide">
				<input class="submit" name="response[submit]" value="submit" type="submit" data-message="">
			</div>
		</div>
	</section>
</div>
</form>