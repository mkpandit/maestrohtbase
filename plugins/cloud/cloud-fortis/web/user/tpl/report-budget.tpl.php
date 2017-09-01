<style>
    #project_tab_ui { display: none; }  /* hack for tabmenu issue */
    /*
    table {
        width: 100%;
        display:block;
    }
    thead {
        display: inline-block;
        width: 100%;
    }
    */
    tbody {
        display: inline-block;
        width: 100%;
        overflow: auto;
        height: 15.4rem;
    }
    tr {
        width: 100%;
        display: table;
    }
</style>
<script>
    var budgetpage = true;
    var datepickeryep = true;
</script>
<div class="cat__content">
    <cat-page>
    <div class="row" id="chart-row">
        <div class="col-sm-12">
            <section class="card">  
                <div class="card-header">
                    <span class="cat__core__title d-inline-block">
                        <label class="d-inline"><strong>Budget Planning</strong></label>
                        <a class="d-inline" id="prev-budget" style="padding: 0 1rem;"><i class="fa fa-backward disabled"></i></a>
                        <h5 class="d-inline" id="budget-name" style="padding: 0 2rem; text-align: center;">BUDGET NAME</h5>
                        <a class="d-inline" id="next-budget" style="padding: 0 1rem;"><i class="fa fa-forward"></i></a>
                    </span>
                    <div class="pull-right">
                    <a id="create-budget" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-budget-modal"><i class="fa fa-plus"></i>&nbsp;Create Budget</a>
                    </div>
                </div>
                <div class="card-block">
                    <div class="row">
                        <div class="col-sm-4 dashboard">
                            <section class="card">  
                                <div class="card-block">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                            <h3 class="panel-title">Budget Resources</h3>
                                        </div>
                                    </div>
                                    <div>
                                        <div id="budgets-setting" style="height: 15rem;">
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <section class="card">  
                                <div class="card-block">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                            <h3 class="panel-title">Budget Alerts</h3>
                                        </div>
                                    </div>
                                    <div>
                                        <div id="budgets-alert" style="height: 15rem;">
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="col-sm-8 dashboard">
                            <section class="card">  
                                <div class="card-block">
                                    <div class="panel-heading">
                                        <div class="panel-control">
                                             <h3 class="panel-title">Spent vs Budget</h3>
                                        </div>
                                    </div>
                                    <div>
                                        <div id="budget-vs-spent-chart" style="height: 34.7rem;">
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    </cat-page>
</div>

<div id="create-budget-modal" class="modal" data-backdrop="static" style="display: none;" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-black">Create A New Budget</h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
        </div>
    </div>
</div>

