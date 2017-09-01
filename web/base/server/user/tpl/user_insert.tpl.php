<!--
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/
//-->
<h2>{label} {name}</h2>

<div id="form">
	{form}
		
	
	<div class="tab-base span7">

 	<ul class="nav nav-tabs">
 									<li class="active">
										<a data-toggle="tab" href="#demo-lft-tab-1">Account</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-2">Personal information</a>
									</li>
									<li>
										<a data-toggle="tab" href="#demo-lft-tab-3">Company information</a>
									</li>									

								</ul>
		
	<div class="tab-content usercreate">
 		
 		<div id="demo-lft-tab-1" class="tab-pane fade active in">
		<h3>Account</h3>
				{user}
				{lang}
				{role}
				{pass1}
				{pass2}
		</div>


		<div id="demo-lft-tab-2" class="tab-pane fade">
		<h3>Personal information</h3>
			{gender}
			{forename}
			{lastname}
				
		</div>

		<div id="demo-lft-tab-3" class="tab-pane fade">
		<h3>Company information</h3>
			{office}
			{department}
			{state}
			{description}
			{capabilities}
				
		</div>
	
	</div>
	</div>	
		
		
		<div class="userbuttons">
		{submit}{cancel}
		</div>
			
	</form>
</div>
