<?php use Core\Lib\Utilities\DateTime; ?>
<?php $this->setSiteTitle("My title here"); ?>

<?php $this->start('body'); ?>
<a href="<?= route('admindashboard.notifications') ?>" class="ms-5 btn btn-sm btn-secondary">Back</a>
<h1 class="text-center">Notification Details</h1>

<div class="d-flex justify-content-center w-75 mx-auto">
    <table class="table table-striped table-bordered table-hover bg-light my-5 col-md-12">
        <tbody>
            <tr>
                <th scope="row" class="text-center w-25">ID</th>
                <td class="text-center" colspan="2"><?= $this->notification->id ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center w-25">Type</th>
                <td class="text-center" colspan="2"><?= $this->notification->type ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center w-25">Notifiable Type</th>
                <td class="text-center" colspan="2"><?= $this->notification->notifiable_type ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center w-25">Notifiable ID</th>
                <td class="text-center" colspan="2"><?= $this->notification->notifiable_id ?></td>
            </tr>
            <tr>
                <th class="text-center align-middle">Data</th>
                <td class="text-start">
                    <?php foreach($this->data as $k => $v): ?>
                        <div><?= htmlspecialchars("\"{$k}\": {$v}") ?></div>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php if($this->notification->read_at): ?>
                <tr>
                    <th scope="row" class="text-center w-25">Read At</th>
                    <td class="text-center" colspan="2"><?= $this->notification->date('read_at')->format(DateTime::FORMAT_FRIENDLY_DATE) ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <th scope="row" class="text-center w-25">Created At</th>
                <td class="text-center" colspan="2"><?= $this->notification->date('created_at')->format(DateTime::FORMAT_HUMAN_READABLE) ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center w-25">Updated At</th>
                <td class="text-center" colspan="2"><?= $this->notification->date('updated_at')->format(DateTime::FORMAT_HUMAN_READABLE) ?></td>
            </tr>
        </tbody>
    </table>
</div>
<?php $this->end(); ?>