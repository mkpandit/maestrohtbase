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
<h2 class="inner chatbotheader">{label}</h2>

<div id="form">
	<div id="demo-panel-w-alert" class="panel">
                    
                                <!--Panel heading-->
                                <div class="panel-heading">
                                    <div class="panel-control">
                                       
                                    </div>
                                    <h3 class="panel-title">ChatBot configuration and execution</h3>
                                </div>
                                
                                <form method="POST">
                                <!--Panel body-->
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="demo-vs-definput" class="control-label">Slack token:</label>
                                        <input id="demo-vs-definput" class="form-control" name="slack" value="{slack}" type="text">
                                    </div>    

                                     <div class="form-group">
                                        <label for="demo-vs-definput" class="control-label">Slack username for important alerts to primary messages:</label>
                                        <input id="demo-vs-definput" class="form-control" name="useralert" value="{useralert}" type="text">
                                    </div>   

                                    <div class="form-group">
                                        <label for="demo-vs-definput" class="control-label">Ip Address:</label>
                                        <input id="demo-vs-definput" class="form-control" name="ip" value="{ip}" type="text">
                                    </div>

                                    <div class="form-group">
                                        <label for="demo-vs-definput" class="control-label">Port:</label>
                                        <input id="demo-vs-definput" class="form-control" name="port" value="{port}" type="text">
                                    </div>

                                    <div class="form-group">
                                        <label for="demo-vs-definput" class="control-label">HTTP Auth Login (login to hypertask system):</label>
                                        <input id="demo-vs-definput" class="form-control" name="login" value="{login}" type="text">
                                    </div>

                                    <div class="form-group">
                                        <label for="demo-vs-definput" class="control-label">HTTP Auth Password (password to hypertask system):</label>
                                        <input id="demo-vs-definput" class="form-control" name="password" value="{password}" type="text">
                                    </div>
                                    <input type="hidden" value="{state}" name="state">
                                    <input type="submit" id="startchatbot" class="btn btn-primary" value="{buttontext}" />
                                    
                                </div>
                                </form>
                            </div>

                                                        
	
</div>
