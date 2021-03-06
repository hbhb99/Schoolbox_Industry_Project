<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\HistoricalFact $historicalFact
 */
?>

<?php
// Helper sort function. Takes an array and returns it in descending order of a 'count' value.
function sortByCountDescending($array)
{
    uasort($array, function ($a, $b) {
        return $b['count'] - $a['count'];
    });
    return $array;
}

// Only show breadcrumbs when not on main page
if ($this->getRequest()->getPath() != '/historical-facts/newest-data') {
    $this->Breadcrumbs->add([
        ['title' => 'Historical Facts', 'url' => ['controller' => 'historical-facts', 'action' => 'index']],
        ['title' => 'Fact Sets', 'url' => ['controller' => 'historical-facts', 'action' => 'index']],
        ['title' => $this->Time->format($historicalFact->timestamp, \IntlDateFormatter::MEDIUM, null), 'url' => ['controller' => 'historicalfacts', 'action' => 'view', $historicalFact->id]]
    ]);
}

// Set page title depending on if we're on the dashboard or just a regular view
if ($this->getRequest()->getPath() == '/historical-facts/newest-data') {
    $this->assign('title', 'Newest Data Set');
} else {
    $this->assign('title', 'Fact Set at ' . $this->Time->format($historicalFact->timestamp, \IntlDateFormatter::MEDIUM, null));
}

?>
<div class="row">
    <div class="col-12">
        <?php
        echo $this->Breadcrumbs->render(
            ['class' => 'breadcrumb'],
            ['separator' => '<i id="breadcrumb-divider" class="fa fa-angle-right"> </i>']
        );
        ?>
        <div class="card mb-4">
            <div class="card-header pb-0">
                <?= $this->Flash->render() ?>
                <div class="row">
                    <div class="col">
                        <h4><?= __('Fact Set') ?> as
                            of <span class="font-weight-bolder"><?= $this->Time->format($historicalFact->timestamp, \IntlDateFormatter::MEDIUM, null) ?></span></h4>
                    </div>
                    <div class="col">
                        <div class="action-buttons pb-2 float-end">
                            <?php if ($this->getRequest()->getPath() != '/historical-facts/newest-data') {
                                if ($this->request->getSession()->read('Auth.isAdmin')) {
                                    echo $this->Form->postLink(__('<i class="fas fa-trash"></i> Delete'), ['action' => 'delete', $historicalFact->id], ['confirm' => __('Are you sure you want to delete the historical fact set for {0}?', $this->Time->format($historicalFact->timestamp, \IntlDateFormatter::MEDIUM, null)), 'class' => 'btn btn-danger mx-1', 'escape' => false]);
                                }
                                echo $this->Html->link(__('<i class="fas fa-list"></i> View all Facts'), ['action' => 'index'], ['class' => 'btn btn-info', 'escape' => false]);
                            } ?>
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="row">
                    <div class="col-12">
                        <div class="input-group px-4">
                            <input type="search" id="accordion_search_bar" class="form-control"
                                   placeholder="Type here to search by fact name!">
                        </div>
                        <div class="row p-4">
                            <div class="col">
                                <!-- Begin Accordion -->
                                <div class="accordion" id="factsAccordion">
                                    <div class="row">
                                        <div class="col" id="accordionColOne">
                                            <!-- Accordion Item (Total Users) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#totalUsersCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Total Users</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="totalUsersCollapse" class="accordion-collapse collapse show"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <?=
                                                            $this->Html->link(number_format(intval(json_decode($historicalFact->schoolbox_totalusers, JSON_PRETTY_PRINT)['totalUsersFleetCount'])), ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_totalusers']], ['class' => 'accordion-results-link']);
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (User Distribution) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#totalUserCountDistributionCollapse"
                                                                aria-expanded="true" aria-controls="totalUsersCollapse">
                                                            <strong>User Count Distribution</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="totalUserCountDistributionCollapse"
                                                         class="accordion-collapse collapse" aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="totalUserCountDistributionTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                            $details = json_decode($historicalFact->schoolbox_totalusers, JSON_PRETTY_PRINT)['totalUsers'];
                                                            // Sort the array by key from low high to low
                                                            krsort($details);
                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>";
                                                                // If the key is 0, then replace with < 1000.
                                                                if ($key == 0) {
                                                                    echo "<td>" . '< 1000' . "</td>";
                                                                    echo "<td>" . $detail . "</td>";
                                                                } else {
                                                                    echo "<td> >" . number_format($key * 1000) . "</td>";
                                                                    echo "<td>" . $detail . "</td>";
                                                                }
                                                                echo "</tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Total Student) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#totalStudentsCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Total Students</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="totalStudentsCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <?=
                                                            $this->Html->link(number_format(intval(json_decode($historicalFact->schoolbox_users_student, JSON_PRETTY_PRINT)['totalStudentCount'])), ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_users_student']], ['class' => 'accordion-results-link']);
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Total Staff) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#totalStaffCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Total Staff</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="totalStaffCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <?=
                                                            $this->Html->link(number_format(intval(json_decode($historicalFact->schoolbox_users_staff, JSON_PRETTY_PRINT)['totalStaffCount'])), ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_users_staff']], ['class' => 'accordion-results-link']);
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Total Parents) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#totalParentsCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Total Parents</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="totalParentsCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <?=
                                                            $this->Html->link(number_format(intval(json_decode($historicalFact->schoolbox_users_parent, JSON_PRETTY_PRINT)['totalParentCount'])), ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_users_parent']], ['class' => 'accordion-results-link']);
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Total Campus) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#totalCampusCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Total Campuses</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="totalCampusCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <?=
                                                            $this->Html->link(number_format(intval(json_decode($historicalFact->schoolbox_totalcampus, JSON_PRETTY_PRINT)['totalCampus'])), ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_totalcampus']], ['class' => 'accordion-results-link']);
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Production Schoolbox Package Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#schoolboxPackageVersionsCollapse"
                                                                aria-expanded="true" aria-controls="totalUsersCollapse">
                                                            <strong>Production 'Schoolbox' Package Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="schoolboxPackageVersionsCollapse"
                                                         class="accordion-collapse collapse" aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="schoolboxPackageVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                                $details = json_decode($historicalFact->schoolbox_package_version, JSON_PRETTY_PRINT)['productionPackageVersions'];

                                                                // Sort the array
                                                                $details = sortByCountDescending($details);

                                                                foreach ($details as $key => $detail) {
                                                                    echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_package_version', 'value' => $key, 'environment' => 'production']]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                                }
                                                                ?>
                                                                </tbody>
                                                            </table>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Production Schoolboxdev Package Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#schoolboxDevPackageVersionsCollapse"
                                                                aria-expanded="true" aria-controls="totalUsersCollapse">
                                                            <strong>Production 'Schoolboxdev' Package Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="schoolboxDevPackageVersionsCollapse"
                                                         class="accordion-collapse collapse" aria-labelledby="totalUsersHeading"
                                                    >
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="schoolboxDevPackageVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $details = json_decode($historicalFact->schoolbox_package_version, JSON_PRETTY_PRINT)['developmentPackageVersions'];

                                                                    // Sort the array
                                                                    $details = sortByCountDescending($details);

                                                                    foreach ($details as $key => $detail) {
                                                                        echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolboxdev_package_version', 'value' => $key, 'environment' => 'production']]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Staging Schoolbox Package Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#stagingSchoolboxPackageVersionsCollapse"
                                                                aria-expanded="true" aria-controls="totalUsersCollapse">
                                                            <strong>Staging 'Schoolbox' Package Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="stagingSchoolboxPackageVersionsCollapse"
                                                         class="accordion-collapse collapse" aria-labelledby="totalUsersHeading"
                                                    >
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="stagingSchoolboxPackageVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>

                                                            <?php
                                                            $details = json_decode($historicalFact->schoolboxdev_package_version, JSON_PRETTY_PRINT)['productionPackageVersions'];

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_package_version', 'value' => $key, 'environment' => 'staging']]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Staging Schoolboxdev Package Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#stagingSchoolboxDevPackageVersionsCollapse"
                                                                aria-expanded="true" aria-controls="totalUsersCollapse">
                                                            <strong>Staging 'Schoolboxdev' Package Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="stagingSchoolboxDevPackageVersionsCollapse"
                                                         class="accordion-collapse collapse" aria-labelledby="totalUsersHeading"
                                                    >
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="stagingSchoolboxDevPackageVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                            <?php
                                                            $details = json_decode($historicalFact->schoolboxdev_package_version, JSON_PRETTY_PRINT)['developmentPackageVersions'];

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolboxdev_package_version', 'value' => $key, 'environment' => 'staging']]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Production Site Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#productionSiteVersionsCollapse"
                                                                aria-expanded="true" aria-controls="totalUsersCollapse">
                                                            <strong>Production Site Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="productionSiteVersionsCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="productionSiteVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                            <?php
                                                            $details = json_decode($historicalFact->schoolbox_config_site_version, JSON_PRETTY_PRINT)['productionServers'];

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_config_site_version', 'value' => $key, 'environment' => 'production']]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";                                                                 }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                        </div>
                                        <div class="col" id="accordionColTwo">
                                            <!-- Accordion Item (Staging Site Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#stagingSiteVersionsCollapse"
                                                                aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Staging Site Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="stagingSiteVersionsCollapse"
                                                         class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="stagingSiteVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                            <?php
                                                            $details = json_decode($historicalFact->schoolbox_config_site_version, JSON_PRETTY_PRINT)['stagingServers'];

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_config_site_version', 'value' => $key, 'environment' => 'staging']]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";                                                                }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Virtual) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#virtualCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Virtual</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="virtualCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="virtualTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                            <?php
                                                            $details = json_decode($historicalFact->virtual, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'virtual', 'value' => $key]]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Linux Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#linuxVersionsCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Linux Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="linuxVersionsCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="linuxVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                <tr>
                                                                    <th>Value</th>
                                                                    <th>Amount</th>
                                                                    <th>Percentage</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                            <?php
                                                            $details = json_decode($historicalFact->lsbdistdescription, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'lsbdistdescription', 'value' => $key]]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Kernel Major Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#kernelVersionsCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Kernel Major Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="kernelVersionsCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="kernelVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                            <?php
                                                            $details = json_decode($historicalFact->kernelmajversion, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
																echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'kernelmajversion', 'value' => $key]]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Kernel Releases) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#kernelReleasesCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Kernel Releases</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="kernelReleasesCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="kernelReleasesTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                            $details = json_decode($historicalFact->kernelrelease, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'kernelrelease', 'value' => $key]]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (PHP CLI Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#phpVersionsCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>PHP CLI Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="phpVersionsCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="phpVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                            $details = json_decode($historicalFact->php_cli_version, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'php_cli_version', 'value' => $key]]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (MySQL Versions) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#mysqlVersionsCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>MySQL Versions</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="mysqlVersionsCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="mysqlVersionsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                            $details = json_decode($historicalFact->mysql_extra_version, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'mysql_extra_version', 'value' => $key]]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Number of Processors) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#processorsCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Number of Processors</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="processorsCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="processorsTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                            $details = json_decode($historicalFact->processorcount, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
																echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'processorcount', 'value' => $key]]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (RAM Size) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#ramSizeCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>RAM Size</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="ramSizeCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="ramSizeTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                            $details = json_decode($historicalFact->memorysize, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $key .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Schoolbox Config - Date / Time) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#dateTimeZoneCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Schoolbox Config - Production Server Date / Timezone</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="dateTimeZoneCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="dateTimeZoneTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                            $details = json_decode($historicalFact->schoolbox_config_date_timezone, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_config_date_timezone', 'value' => $key, 'environment' => 'production']]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Schoolbox Config - External DB Type) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#externalDbCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Schoolbox Config - Production External DB Types</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="externalDbCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="externalDbTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                            $details = json_decode($historicalFact->schoolbox_config_external_type, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_config_external_type', 'value' => $key, 'environment' => 'production']]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- Accordion Item (Schoolbox - First File Upload Date) Begin -->
                                            <div class="card mb-3">
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="totalUsersHeading">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#firstFileCollapse" aria-expanded="true"
                                                                aria-controls="totalUsersCollapse">
                                                            <strong>Schoolbox - First File Upload Year</strong>
                                                            <i class="fa text-xs pt-1 position-absolute end-0 me-3"></i>
                                                        </button>
                                                    </h2>
                                                    <div id="firstFileCollapse" class="accordion-collapse collapse"
                                                         aria-labelledby="totalUsersHeading">
                                                        <div id="accordion-divider" class="accordion-divider"></div>
                                                        <div class="accordion-body">
                                                            <table id="firstFileTable" class="table table-responsive table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Value</th>
                                                                        <th>Amount</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                            $details = json_decode($historicalFact->schoolbox_first_file_upload_year, JSON_PRETTY_PRINT);

                                                            // Sort the array
                                                            $details = sortByCountDescending($details);

                                                            foreach ($details as $key => $detail) {
                                                                echo "<tr>
                                                                            <td>" . $this->Html->link($key, ['controller' => 'Facts', 'action' => 'factDetails', '?' => ['fact' => 'schoolbox_first_file_upload_year', 'value' => $key]]) .  "</td>
                                                                            <td>" . $detail['count'] . "</td>
                                                                            <td>" . $detail['percent'] . "%" . "</td>
                                                                          </tr>";
                                                            }
                                                            ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Accordion Item -->
                                            <!-- End Accordion -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>

        // Search functionality for accordion
        (function () {
            $('#accordion_search_bar').on('change keyup paste click', function () {
                var filter = $(this).val().toLowerCase(); //get text
                $("#factsAccordion [data-bs-toggle]").each(function () {
                    if ($(this).text().toLowerCase().trim().indexOf(filter) < 0) {
                        $(this).closest(".card").hide(200); //hide closest card
                        $('#accordionColOne').removeClass('col').addClass('col-0');
                        $('#accordionColTwo').removeClass('col').addClass('col-0');

                    } else {
                        $(this).closest(".card").show(200)
                        $('#accordionColOne').removeClass('col-0').addClass('col');
                        $('#accordionColTwo').removeClass('col-0').addClass('col');
                    }
                });
            });
        }());

        /*
            'scrollX: true' doesn't actually handle resizing according to the size of the container
            So, we use "initComplete" to wrap the table in a container that is relatively positioned
         */

        // DataTable configuration
        $(document).ready(() => {
            $('#totalUserCountDistributionTable').DataTable({
                paging: false,
                order: [[1, 'desc']],
                info: false
            })
            $('#schoolboxPackageVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#schoolboxPackageVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#schoolboxDevPackageVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#schoolboxDevPackageVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#stagingSchoolboxPackageVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#stagingSchoolboxPackageVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#stagingSchoolboxDevPackageVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#stagingSchoolboxDevPackageVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#productionSiteVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#productionSiteVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#stagingSiteVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#stagingSiteVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#virtualTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#virtualTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#linuxVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#linuxVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#kernelVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#kernelVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#kernelReleasesTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#kernelReleasesTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#phpVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#phpVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#mysqlVersionsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#mysqlVersionsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#processorsTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#processorsTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#ramSizeTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                columnDefs: [ { targets: 0, type: 'natural' } ],
                "initComplete": function (settings, json) {
                    $("#ramSizeTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#dateTimeZoneTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#dateTimeZoneTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#externalDbTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#externalDbTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })
            $('#firstFileTable').DataTable({
                paging: true,
                language: {
                    'paginate': {
                        'next': '>',
                        'previous': '<'
                    }
                },
                order: [[2, 'desc']],
                info: true,
                "initComplete": function (settings, json) {
                    $("#firstFileTable").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
                },
            })

        })

    </script>
