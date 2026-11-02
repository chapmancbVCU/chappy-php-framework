<?php $this->setSiteTitle("Manage ACLs"); ?>
<?php $this->start('body'); ?>
<h1 class="text-center">Manage ACLs
    <a href="<?=route('admindashboard.editAcl', ['new'])?>" class="btn btn-primary btn-sm me-5">
        <i class="fa fa-plus"></i> Add ACL
    </a>
</h1>
<?= $this->unusedPagination->paginationSummary('acls') ?>
<table class="table table-striped table-bordered table-hover mb-5">
    <thead>
        <th class="text-center">ACL</th>
        <th class="text-center">Created</th>
        <th class="text-center">Updated</th>
        <th></th>
    </thead>
    <tbody>
        <?php foreach($this->unUsedAcls as $acl): ?>
            <tr> 
                <td class="text-center w-50"><?= $acl->acl ?></td>
                <td class="text-center"><?= $acl->date('created_at')->diffForHumans() ?></td>
                <td class="text-center"><?= $acl->date('updated_at')->diffForHumans() ?></td>
                <td class="text-center">
                    <a href="<?=route('admindashboard.editAcl', [$acl->id])?>" class="btn btn-info btn-sm">
                        <i class="fa fa-edit"></i> Edit ACL
                    </a>
                    <form method="POST" 
                        action="<?=route('admindashboard.deleteAcl')?>" 
                        class="d-inline-block" 
                        onsubmit="if(!confirm('Are you sure?')){return false;}">
                        <?= hidden('id', $acl->id) ?>
                        <?= $csrfToken = csrf() ?>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->unusedPagination->render() ?>

<h2 class="text-center">ACLs In Use</h2>
<?= $this->usedPagination->paginationSummary('acls') ?>
<table class="table table-striped  table-bordered table-hover">
    <thead>
        <th class="text-center">ACL</th>
        <th class="text-center">Created</th>
        <th class="text-center">Updated</th>
    </thead>
    <tbody>
        <?php foreach($this->usedAcls as $acl): ?>
            <tr>
                <td class="text-center w-50"><?= $acl->acl ?></td>
                <td class="text-center"><?= $acl->date('created_at')->diffForHumans() ?></td>
                <td class="text-center"><?= $acl->date('updated_at')->diffForHumans() ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->usedPagination->render() ?>
<div class="widget-container">
    <?= $this->renderWidgets('dashboard.acls', $this->widgets) ?>
</div>

<?php $this->end(); ?>