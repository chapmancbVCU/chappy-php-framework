<?php $this->setSiteTitle("Notifications"); ?>
<?php $this->start('body'); ?>
<h1 class="text-center">Notifications</h1>

<?php if($this->notifications): ?>
    <div class="d-flex justify-content-center">
        <table class="w-75 table table-striped table-bordered table-hover table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($this->notifications as $notification): ?>
                    <tr>
                        <td><?= $notification->id ?></td>
                        <td><?= $notification->notification_type ?></td>
                        <td><?= $notification->date('created_at')->diffForHumans() ?></td>
                        <td><?= $notification->date('updated_at')->diffForHumans() ?></td>
                        <td class="text-end">
                            <a href="<?= route('admindashboard.notificationDetails', [$notification->id]) ?>"
                                class="btn btn-primary btn-sm"><i class="fa fa-info"></i> Details
                            </a>
                            <form method="POST"
                                action="<?= route('admindashboard.deleteNotification', [$notification->id]) ?>"
                                class="d-inline-block"
                                onsubmit="return confirm('Are you sure you want to delete this notification')">
                                <?= csrf() ?>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i>Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <?= $this->pagination->render(); ?>
<?php else: ?>
    <h3 class="text-center">There are no pending notifications in the database channel.</h3>
<?php endif; ?>
<?php $this->end(); ?>