<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use DateTime;

/**
 * HistoricalFacts Controller
 *
 * @property \App\Model\Table\HistoricalFactsTable $HistoricalFacts
 * @method \App\Model\Entity\HistoricalFact[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class HistoricalFactsController extends AppController
{

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->loadModel('HistoricalFacts');

        // Capture query params if passed
        $queryString = '?';
        foreach ($this->request->getQueryParams() as $key => $value) {
            $queryString .= $key . '=' . $value . '&';
        }
        $queryString = Substr_replace($queryString, "", -1);

        $path = $this->request->getPath();
        $userEmail = $this->request->getSession()->read('Auth.email');
        if ($userEmail == null) {
            $this->Flash->error("Please sign in first...");
            $this->redirect('/users/login?redirect=' . $path . $queryString);
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // Redirect POST to GET
        if ($this->request->is('POST')) {
            $date = $this->request->getData('date');
            $this->redirect(['action' => 'index', '?' => ['date' => $date]]);
        }

        // Get the date value and check if it's set
        $date = $this->request->getQuery('date');

        // If date value is provided, then paginate only that date provided
        if (isset($date)) {
            $historicalFacts = $this->paginate(
                $this->HistoricalFacts->find('all')->where(['HistoricalFacts.timestamp LIKE' => "%" . $this->request->getQuery('date') . "%"]),
                [
                    'order' => ['HistoricalFacts.id' => 'desc'],
                    'limit' => 48 // 30 min intervals = max of 48 records a day, easiest to show them all on one page
                ]
            );
            $this->set(compact('historicalFacts'));

            // Display a flash message to make it clear that this is not all the records.
            $this->Flash->success("You are viewing the fact sets for " . date('d/m/Y', strtotime($date)) . ".", ['params' =>
                    [
                        'text' => 'View all facts?',
                        'controller' => 'HistoricalFacts',
                        'action' => 'index'
                    ]
                ]
            );
        // If no date provided, paginate all data and send it to the view as usual
        } else {
            $historicalFacts = $this->paginate($this->HistoricalFacts, [
                'order' => ['HistoricalFacts.id' => 'desc']
            ]);
            $this->set(compact('historicalFacts'));
        }

        // Get the earliest timestamp in the entire list of historical facts
        $this->set('earliestDate', $this->HistoricalFacts->find('all')->first()->timestamp->format('Y-m-d'));

        // Get the date provided in the query and set is a view variable
        $this->set('date', $date);
    }

    /**
     * View method
     *
     * @param string|null $id Historical Fact id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $historicalFact = $this->HistoricalFacts->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('historicalFact'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Historical Fact id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        // Obtain user type from session object
        $userIsAdmin = $this->request->getSession()->read('Auth.isAdmin');

        // If the user is an admin, allow delete, otherwise flash an error
        if ($userIsAdmin) {
            $historicalFact = $this->HistoricalFacts->get($id);
            if ($this->HistoricalFacts->delete($historicalFact)) {
                $this->Flash->success(__('The historical fact has been deleted.'));
            } else {
                $this->Flash->error(__('The historical fact could not be deleted. Please, try again.'));
            }
        } else {
            $this->Flash->error(__('Deleting historical fact sets requires administrator rights. Please contact an administrator if you think this is an error.'));
        }


        return $this->redirect(['action' => 'index']);
    }

    /**
     * Newest Data Method
     *
     * Retrieves the newest entry in the DB
     */
    public function newestData() {
        $historicalFact = $this->HistoricalFacts->find('all', [
            'order' => ['id' => 'DESC']
        ]);

        // Get the first item in the list
        $historicalFact = $historicalFact->first();

        // Display a flash message when showing the most recent dataset
        $this->Flash->success("You are viewing the most recently cached dataset!");

        $this->set(compact('historicalFact'));
        $this->render('view');
    }
}
