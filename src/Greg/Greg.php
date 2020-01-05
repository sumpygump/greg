<?php

namespace Greg;

/**
 * Greg kernel class
 *
 * @package Greg
 * @author Jansen Price <jansen.price@gmail.com>
 */
class Greg
{
    const VERSION = '1.0';

    // Goal statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';

    // Progress statuses
    const PSTATUS_ONTRACK = 'on-track';
    const PSTATUS_SLIPPING = 'slipping';
    const PSTATUS_FAILING = 'failing';

    const START_DATE = '2019-12-01';

    /**
     * Progress statuses
     *
     * @var array
     */
    public static $progress_statuses = [
        '1' => self::PSTATUS_ONTRACK,
        '2' => self::PSTATUS_SLIPPING,
        '3' => self::PSTATUS_FAILING,
    ];

    /**
     * Configuration array
     *
     * @var array
     */
    public $config;

    /**
     * Definition of goals data filename
     *
     * @var string
     */
    public $goals_db_file = 'goals.json';

    /**
     * Calculated full path to data file
     *
     * @var string
     */
    public $goals_db;

    /**
     * List of goals
     *
     * @var array
     */
    public $goals = [];

    /**
     * Constructor
     *
     * @param array $config
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;

        $this->goals_db = $this->config['base_dir'] . '/' . $this->goals_db_file;

        if (!is_dir(dirname($this->goals_db))) {
            mkdir(dirname($this->goals_db));
        }

        $this->loadGoals();
    }

    /**
     * Load goals from data file
     *
     * @return void
     */
    public function loadGoals()
    {
        if (!file_exists($this->goals_db)) {
            return false;
        }

        $this->goals = json_decode(file_get_contents($this->goals_db));
    }

    /**
     * Add a goal to the list and save data file
     *
     * @param string $goal_text
     * @return object Resulting goal object
     */
    public function addGoal($goal_text)
    {
        $goal = [
            'id' => random_int(10000, 99999),
            'name' => $goal_text,
            'status' => self::STATUS_ACTIVE,
            'pstatus' => '',
            'progress' => [],
            'date_created' => date('Y-m-d H:i:s'),
            'date_completed' => '',
        ];

        $this->goals[] = $goal;
        $this->saveGoals();

        // to turn into object
        return json_decode(json_encode($goal));
    }

    /**
     * Save all goals to data file
     *
     * @return void
     */
    public function saveGoals()
    {
        $data = json_encode($this->goals, JSON_PRETTY_PRINT);

        file_put_contents($this->goals_db, $data);
    }

    /**
     * Get all active goals
     *
     * @return array
     */
    public function getActiveGoals()
    {
        $active_goals = [];

        foreach ($this->goals as $goal) {
            if ($goal->status == self::STATUS_ACTIVE) {
                $active_goals[] = $goal;
            }
        }

        return $active_goals;
    }

    /**
     * Get the goal for today
     *
     * @return object
     */
    public function getTodaysGoal()
    {
        $goals = $this->getActiveGoals();

        srand(strtotime('midnight'));
        $goal_len = count($goals);

        $target = rand(0, $goal_len - 1);
        return $goals[$target];
    }

    /**
     * Convert a goal object to string
     *
     * @param object $goal
     * @param string $prefix
     * @return string
     */
    public function goalToString($goal, $prefix = '')
    {
        $pstatus = '';
        if (isset($goal->pstatus) && $goal->pstatus) {
            $pstatus = " ~" . $goal->pstatus;
        }

        $last_progress = '';
        if (isset($goal->progress) && count($goal->progress) > 0) {
            $last_progress = $goal->progress[count($goal->progress) - 1];
        }

        $progress_date = '';
        if ($pstatus && $last_progress) {
            $progress_date = " (as of " . date('d M Y', strtotime($last_progress->datetime)) . ")";
        }

        return sprintf("%s%s [%s]%s%s\n", $prefix, $goal->name, $goal->id, $pstatus, $progress_date);
    }

    /**
     * Find a goal from in memory list by its id or index
     *
     * Returns resulting goal object or false if not found
     *
     * @param int $id
     * @return object|false
     */
    public function getGoalById($id)
    {
        $goals = $this->getActiveGoals();

        // Try to find by id
        foreach ($goals as $i => $goal) {
            if ($goal->id == $id) {
                return $goal;
            }
        }

        // Try to find by active index
        foreach ($goals as $i => $goal) {
            if ($i + 1 == $id) {
                return $goal;
            }
        }

        return false;
    }

    public function addProgress($input_goal, $record)
    {
        // TODO I could just index the goals array by goal id and then I don't
        // need to cycle through to find it
        foreach ($this->goals as &$goal) {
            if ($input_goal->id == $goal->id) {
                if (!isset($goal->progress)) {
                    $goal->progress = [];
                }

                $goal->pstatus = $record['status'];
                $goal->progress[] = $record;
            }
        }

        $this->saveGoals();
    }

    /**
     * Mark a goal as complete and save goals to data file
     *
     * @param object $input_goal Goal object
     * @return void
     */
    public function markComplete($input_goal)
    {
        // TODO I could just index the goals array by goal id and then I don't
        // need to cycle through to find it
        foreach ($this->goals as &$goal) {
            if ($input_goal->id == $goal->id) {
                $goal->status = self::STATUS_COMPLETED;
                $goal->date_completed = date('Y-m-d H:i:s');
            }
        }

        $this->saveGoals();
    }
}
