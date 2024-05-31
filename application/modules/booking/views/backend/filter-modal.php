<!--  Model popup : begin-->
<div class="modal fade" id="modal-default-filter" role="dialog" style="overflow:hidden;">
    <div class="modal-dialog">
        <form action="<?= current_url() ?>" method="get" id="form-bookings-filter">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?= Translate::sprint("Filter bookings") ?> </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?= _lang("Store") ?></label>
                                <input type="text" class="form-control" name="store"
                                       placeholder="<?= _lang('Store name') ?>"
                                       value="<?= RequestInput::get('store') ?>"/>
                            </div>
                            <div class="form-group">
                                <label><?= _lang("Client name") ?></label>
                                <input type="text" class="form-control" name="client"
                                       placeholder="<?= _lang('Client name') ?>"
                                       value="<?= RequestInput::get('client') ?>"/>
                            </div>
                            <?php if ($this->uri->segment(3) == "all_bookings"
                                && GroupAccess::isGranted('booking', GRP_MANAGE_BOOKING_CONFIG)): ?>
                                <div class="form-group">
                                    <label><?= _lang("Business Owner") ?></label>
                                    <select id="owner" data-name="owner" class="form-control select2"
                                            multiple>
                                    </select>
                                </div>
                                <input type="hidden" name="owner"
                                       value="<?= RequestInput::get('owner') ?>">
                            <?php endif; ?>


                            <div class="form-group">
                                <label><?= _lang("Select date") ?></label>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control datepicker" name="date_start"
                                               placeholder="<?= _lang('Date start') ?>"
                                               value="<?= RequestInput::get('date_start') ?>"/>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control datepicker" name="date_end"
                                               placeholder="<?= _lang('Date end') ?>"
                                               value="<?= RequestInput::get('date_end') ?>"/>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group">
                                <label><?= _lang("Booking Status") ?></label>
                                <?php
                                $sc = parseUrlParam('booking_status');
                                ?>
                                <select id="booking_status"
                                        class="form-control select2"
                                        data-name="booking_status"
                                        name="booking_status"
                                        multiple>
                                    <option value="0" <?= in_array(0, $sc) ? "selected" : "" ?>><?= _lang("Pending") ?></option>
                                    <option value="1" <?= in_array(1, $sc) ? "selected" : "" ?>><?= _lang("Confirmed") ?></option>
                                    <option value="-1" <?= in_array(-1, $sc) ? "selected" : "" ?>><?= _lang("Canceled") ?></option>
                                </select>
                                <input type="hidden" name="booking_status" value="">
                            </div>

                            <div class="form-group">
                                <label><?= _lang("Payment Status") ?></label>

                                <?php
                                $sc = parseUrlParam('payment_status');
                                ?>

                                <select id="select_payment_status"
                                        class="form-control select2"
                                        name="payment_status"
                                        data-name="payment_status"
                                        multiple>
                                    <?php foreach (Booking_payment::PAYMENT_STATUS as $k => $ps): ?>
                                        <option value="<?= $k ?>" <?= in_array($k, $sc) ? "selected" : "" ?> ><?= _lang($ps['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="payment_status" value="">
                            </div>
                            <div class="form-group">
                                <label><?= _lang("Limit") ?></label>
                                <select class="form-control select2" name="limit" id="limit">
                                    <option value="30" <?= RequestInput::get('limit') == 30 ? "selected" : "" ?>>30
                                    </option>
                                    <option value="60" <?= RequestInput::get('limit') == 60 ? "selected" : "" ?>>60
                                    </option>
                                    <option value="100" <?= RequestInput::get('limit') == 100 ? "selected" : "" ?>>100
                                    </option>
                                    <option value="200" <?= RequestInput::get('limit') == 200 ? "selected" : "" ?>>200
                                    </option>
                                    <option value="300" <?= RequestInput::get('limit') == 300 ? "selected" : "" ?>>300
                                    </option>
                                    <option value="500" <?= RequestInput::get('limit') == 500 ? "selected" : "" ?>>500
                                    </option>
                                    <option value="700" <?= RequestInput::get('limit') == 700 ? "selected" : "" ?>>700
                                    </option>
                                    <option value="1000 <?= RequestInput::get('limit') == 1000 ? "selected" : "" ?>">
                                        1000
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left"
                            data-dismiss="modal"><?= Translate::sprint("Cancel") ?></button>
                    <button type="button" id="_filter"
                            data=""
                            class="btn btn-flat btn-primary"><?= Translate::sprint("Apply") ?></button>
                </div>
            </div>
        </form>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--  Model popup : end-->


<?php
$script = $this->load->view('booking/backend/scripts/filter-modal-script', NULL, TRUE);
AdminTemplateManager::addScript($script);


