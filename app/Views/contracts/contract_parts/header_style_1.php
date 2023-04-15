<div class="row">
    <div class="col-md-5 mb15">
        <?php
        $data = array(
            "client_info" => $client_info,
            "color" => $color,
            "contract_info" => $contract_info
        );

        echo view('contracts/contract_parts/contract_from', $data);
        ?>
    </div>
    <div class="col-md-3">
        <?php
        echo view('contracts/contract_parts/contract_to', $data);
        ?>
    </div>
    <div class="col-md-4 text-right">
        <?php
        echo view('contracts/contract_parts/contract_info', $data);
        ?>
    </div>
</div>