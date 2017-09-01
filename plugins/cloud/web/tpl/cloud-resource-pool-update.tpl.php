<!--
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
    {*cloud_resource_pool_assign*}
     {cloud_resource_pool_assign}
 

*/
-->
<h2>{title}</h2>
<form action="{thisfile}">
{form}

<div class="row hprow">
															  <div class="col-xs-5">
															  
															    <select name="from" id="hpselect" class="form-control notselectpicker" size="8" multiple="multiple">
															      {hpoptions}
															    </select>
															  </div>
															  <div class="col-xs-2">
															    <button type="button" id="hpselect_rightAll" class="btn btn-block"><i class="fa fa-long-arrow-right fa-2x"></i></button>
															    <button type="button" id="hpselect_rightSelected" class="btn btn-block"><i class="fa fa-long-arrow-right fa-lg"></i></button>
															    <button type="button" id="hpselect_leftSelected" class="btn btn-block"><i class="fa fa-long-arrow-left fa-lg"></i></button>
															    <button type="button" id="hpselect_leftAll" class="btn btn-block"><i class="fa fa-long-arrow-left fa-2x"></i></button>
															  </div>
															  <div class="col-xs-5">
															    <select name="to" id="hpselect_to" class="form-control notselectpicker" size="8" multiple="multiple">
															    </select>
															  </div>
															</div>
    {cloud_resource_pool_assign}

<div id="buttons" class="hpselectbtn">{submit}&#160;{cancel}</div>
</form>

