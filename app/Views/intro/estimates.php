
<style>
    .list-info {
        list-style-type: none; 
    }
    .list-info li {
        padding:10px 10px 10px 0;
    }
    .list-info li svg {
        margin-right:  20px;
    }

    .intro-image{
        max-width: 500px;
    }

    .intro-container {
        max-width: 500px;
        margin: auto;
    }
    .close-intro {
        right: 20px;
        top: 20px;
        position: absolute; 
        padding: 10px;
        cursor: pointer;
    }
    
</style>
<div class="card p20">
    <div class="intro-container">
        <span class="js-hide-intro close-intro" data-type="estimates"><i data-feather="x-circle" class="icon"></i></span>
        <img src="<?php echo base_url('assets/images/intro/grow.png'); ?>" class="intro-image"/>
        <h4 class="pl15 pt10 pr15"> Want to get more projects/leads?</h4>
        <ul id="expenses-tabs" class="list-info">
            <li><i data-feather="file-plus" class="icon"></i> Create estimate forms.</li>
            <li><i data-feather="share-2" class="icon"></i> Share embeded estimate form in external website.</li>
            <li><i data-feather="send" class="icon"></i> Let your clients to submit estimate requests.</li>
            <li><i data-feather="disc" class="icon"></i> Observe the estimate requests.</li>
            <li><i data-feather="pen-tool" class="icon"></i> Make estimates and send to your clients.</li>
            <li><i data-feather="check-square" class="icon"></i> Get approval.</li>
            <li><i data-feather="play-circle" class="icon"></i> Start new projects.</li>
            <li><i data-feather="file-text" class="icon"></i> Issue invoices.</li>
            <li><i data-feather="compass" class="icon"></i> Get paid.</li>

        </ul>
    </div> 

</div>
