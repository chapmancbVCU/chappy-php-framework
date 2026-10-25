<?php $this->setSiteTitle("Queue Manager"); ?>
<?php $this->start('body'); ?>
<h1 class="text-center mb-5">Queue Manager</h1>

<?php if($this->queue): ?>
    <div class="d-flex justify-content-center">
        <table class="w-75 table table-striped table-bordered table-hover table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Queue</th>
                    <th>Attempts</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($this->queue as $job): ?>
                    <tr>
                        <td><?= $job->id ?></td>
                        <td><?= $job->queue ?></td>
                        <td><?= $job->attempts ?></td>
                        <td><?= $job->date('created_at')->diffForHumans() ?></td>
                        <td><?= $job->date('updated_at')->diffForHumans() ?></td>
                        <td class="text-end">
                            <a href="<?= route('admindashboard.jobDetails', [$job->id]) ?>"
                                class="btn btn-primary btn-sm"><i class="fa fa-info"></i> Details
                            </a>
                            <form method="POST"
                                action="<?= route('admindashboard.deleteJob', [$job->id]) ?>"
                                class="d-inline-block"
                                onsubmit="return confirm('Are you sure you want to delete this job?')">
                                <?= csrf() ?>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->pagination->render(); ?>
<?php else: ?>
    <h3 class="text-center">There are no pending jobs in the queue table.</h3>
<?php endif; ?>
<?php $this->end(); ?>