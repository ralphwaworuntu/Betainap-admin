<div class="row store-service">
    <!-- text input -->
    <div class="col-sm-12 service-list">
        <div class="row">
            <div class="col-md-12">
                <?php
                $groups = $this->mService->laodServices($id);
                ?>
                <?php if(!empty($groups)): ?>
                <div class="row" id="grp-service-container">
                <?php

                    foreach ($groups as $grp){
                        $data['grp'] = $grp;
                        $this->load->view('service/plugV2/options/group_row',$data);
                    }

                ?>
                </div>
                <?php else: ?>
                    <div class="row" id="grp-service-container">
                        <span><?=_lang("No Service/Menu Added")?> <a class="create-new-grp-service" href="#"><?=_lang("Start adding service/menu groups")?></a></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>



    </div>

</div>

<?php

$modal1 = $this->load->view("service/plugV2/modal-create-grp",NULL,TRUE);
$modal2 = $this->load->view("service/plugV2/modal-create-option",NULL,TRUE);
AdminTemplateManager::addHtml($modal1);
AdminTemplateManager::addHtml($modal2);
