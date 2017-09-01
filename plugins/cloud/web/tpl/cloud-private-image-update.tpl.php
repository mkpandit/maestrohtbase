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
<h2>{title}</h2>
<form action="{thisfile}">
{form}
<div class="row pirow">
															  <div class="col-xs-5">
															  
															    <select name="from" id="piselect" class="form-control notselectpicker" size="8" multiple="multiple">
															      {pioptions}
															    </select>
															  </div>
															  <div class="col-xs-2">
															    <button type="button" id="piselect_rightAll" class="btn btn-block"><i class="fa fa-long-arrow-right fa-2x"></i></button>
															    <button type="button" id="piselect_rightSelected" class="btn btn-block"><i class="fa fa-long-arrow-right fa-lg"></i></button>
															    <button type="button" id="piselect_leftSelected" class="btn btn-block"><i class="fa fa-long-arrow-left fa-lg"></i></button>
															    <button type="button" id="piselect_leftAll" class="btn btn-block"><i class="fa fa-long-arrow-left fa-2x"></i></button>
															  </div>
															  <div class="col-xs-5">
															    <select name="to" id="piselect_to" class="form-control notselectpicker" size="8" multiple="multiple">
															    </select>
															  </div>
															</div>
{cloud_private_image_assign}
<div id="buttons">{submit}&#160;{cancel}</div>
</form>

