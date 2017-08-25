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
-->
			<style>
			.login-container {
				text-align: left;
				display: block;
				width: 100vw;
			}
			.login-container .form-group {
				width: 50%;
				margin: 0 auto;
				display: block;
				padding: 1.5em 0;
				
			}
			.login-container .form-group input {
				padding: 10px;
				height: 34px;
			}
			.form-control {
				/* background-color: rgb(226,226,226); */
				background-position: center center;
				color: rgb(0,0,0);
				font-size: 1.2em;
				border-radius: 0.3rem;
				font-weight: 400;
			}
			.form-group a {
				color: rgb(255,255,255);
				font-family: sans-serif;
				font-size: 1.0em;
				font-weight: lighter;
				padding-top: 0.8em;
				padding-right: 0.2em;
				text-decoration: underline;
				display: inline-block;
			}
			.form-group a:last-of-type {
				padding-right: 0;
			}
			input:-webkit-autofill {
				background-color: rgb(255,255,255) !important;
			}
			input:focus {
				border-color: rgb(228,233,240) !important;
			}
			.htmlobject_tabs {
				margin: -25px;
				z-index: 10;
			}
			.htmlobject_tabs > ul {
				width: 30.71rem;
				margin: 0 auto;	
				list-style: none;
				padding-top: 20px;
			}
			.htmlobject_tabs > ul > li {
				display: inline;
				margin: 0 15px 0 0;
			}
			.htmlobject_tabs > ul > li a {
				color: rgb(255,255,255);
			}
			.htmlobject_tabs > ul > li:first-of-type, .htmlobject_tabs > ul > li:last-of-type {
				display: none;
			}
			.htmlobject_tabs_box .msgBox{
				text-align: center;
			}
			</style>
			<div class="login-container">
				<div class="cat__pages__login__block__form">
					<form id="form-validation" method="POST" name="form-validation" autocomplete="off">
						<div class="form-group">
							<input class="form-control" id="userlogin" placeholder="Username" type="text" autocomplete="off">
						</div>
						<div class="form-group">
							<div class="input-append input-group">
								<input class="form-control password" id="userpassword" placeholder="Password" type="password" autocomplete="off">
								<span tabindex="100" title="Click here to show/hide password" id="btnlogin" class="add-on input-group-addon" style="cursor: pointer;"><i class="fa fa-arrow-right"></i></span>
							</div>
						</div>
					</form>
				</div>
			</div>




			
		
