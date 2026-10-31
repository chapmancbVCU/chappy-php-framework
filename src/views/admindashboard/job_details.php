<?php use Core\Lib\Utilities\DateTime; ?>
<?php $this->setSiteTitle("Queued Job Details"); ?>
<?php $this->start('body'); ?>
<a href="<?= route('admindashboard.queueManager') ?>" class="ms-5 btn btn-sm btn-secondary">Back</a>
<h1 class="text-center">Queued Job Details</h1>

<div class="d-flex justify-content-center w-75 mx-auto">
    <table class="table table-striped table-bordered table-hover bg-light my-5">
        <tbody>
            <tr>
                <th scope="row" class="text-center w-25">ID</th>
                <td class="text-center" colspan="2"><?= $this->job->id ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center w-25">Queue</th>
                <td class="text-center" colspan="2"><?= htmlspecialchars($this->job->queue) ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center align-middle w-25" rowspan="4">Payload</th>
                <td class="text-end">"job":</td>
                <td class="text-start"><?= htmlspecialchars($this->payload->job) ?></td>
            </tr>
            <tr>
                <td class="text-end">"data":</td>
                <td class="text-start">
                    <?php foreach($this->payload->data as $k => $v): ?>
                        <div><?= htmlspecialchars("\"{$k}\": {$v}") ?></div>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <td class="text-end">"available_at":</td>
                <td class="text-start">
                    <?= DateTime::formatTime($this->payload->available_at, DateTime::FORMAT_HUMAN_READABLE) ?>
                </td>
            </tr>
            <tr>
                <td class="text-end">"max_attempts":</td>
                <td class="text-start"><?= $this->payload->max_attempts ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center w-25">Attempts</th>
                <td class="text-center" colspan="2"><?= $this->job->attempts ?></td>
            </tr>
            <?php if($this->job->reserved_at): ?>
                <tr>
                    <th scope="row" class="text-center w-25">Reserved At</th>
                    <td class="text-center" colspan="2"><?= $this->job->reserved_at ?></td>
                </tr>
            <?php endif; ?>
            <?php if($this->job->failed_at): ?>
                <tr>
                    <th scope="row" class="text-center w-25">Failed At</th>
                    <td class="text-center" colspan="2"><?= $this->job->failed_at ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <th scope="row" class="text-center w-25">Available At</th>
                <td class="text-center" colspan="2"><?= $this->job->date('available_at')->format(DateTime::FORMAT_FRIENDLY_DATE) ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center w-25">Created At</th>
                <td class="text-center" colspan="2"><?= $this->job->date('created_at')->format(DateTime::FORMAT_HUMAN_READABLE) ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center w-25">Updated At</th>
                <td class="text-center" colspan="2"><?= $this->job->date('updated_at')->format(DateTime::FORMAT_HUMAN_READABLE) ?></td>
            </tr>
            <tr>
                <th scope="row" class="text-center w-25">Exception</th>
                <td class="text-start" colspan="2"><pre class="mb-0 text-wrap"><?= htmlspecialchars((string)$this->job->exception) ?></pre></td>
            </tr>
        </tbody>
    </table>
</div>
<?php $this->end(); ?>