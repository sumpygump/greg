<?php

namespace Greg;

use Greg\Exception\GoalNotFoundException;
use Qi_Console_Tabular;

/**
 * Client
 *
 * @package Greg
 * @author Jansen Price <jansen.price@gmail.com>
 */
class Client
{
    /**
     * Greg kernel object
     *
     * @var Greg
     */
    public $greg;

    /**
     * Constructor
     *
     * @param Greg $greg
     * @return void
     */
    public function __construct(Greg $greg)
    {
        $this->greg = $greg;
    }

    /**
     * list
     *
     * @return int
     */
    public function list($as_table = false)
    {
        $goals = $this->greg->getActiveGoals();
        if (empty($goals)) {
            print "No goals set. Set one with `greg add <goal>`\n";
            return 1;
        }

        if ($as_table) {
            $this->displayListTable($goals);
        } else {
            foreach ($goals as $i => $goal) {
                print $this->greg->goalToString($goal, $i+1 . ". ");
            }
        }

        return 0;
    }

    /**
     * Display goals in a table
     *
     * @param array $goals
     * @return void
     */
    private function displayListTable($goals)
    {
        $headers = ["Goal", "Id", "Status", "Last comment"];
        $table = [];
        foreach ($goals as $i => $goal) {
            $table[] = [
                $i + 1 . ". " . $goal->name,
                $goal->id,
                $this->greg->getPStatus($goal),
                $this->greg->getLastComment($goal),
            ];
        }

        $tabular = new Qi_Console_Tabular(
            $table,
            ['headers' => $headers]
        );
        $tabular->display();
    }

    /**
     * add
     *
     * @param array $argv
     * @return int
     */
    public function add($argv = [])
    {
        if (!isset($argv[2])) {
            print "Enter the goal you would like to add: ";
            $goal_text = rtrim(fgets(STDIN));
        } else {
            // collect all args
            $goal_text = implode(' ', array_slice($argv, 2));
        }

        if (trim($goal_text) == '') {
            print "You cannot add an empty goal.\n";
            return 1;
        }

        $goal = $this->greg->addGoal($goal_text);
        print "Added new goal:\n";
        print $this->greg->goalToString($goal, "✱ ");

        return 0;
    }

    /**
     * remind
     *
     * @return int
     */
    public function remind()
    {
        $goal = $this->greg->getTodaysGoal();
        printf("Greg's goal reminder for today (%s):\n", date('Y-m-d'));
        print $this->greg->goalToString($goal, "◈ ");

        return 0;
    }

    /**
     * complete
     *
     * @param array $argv
     * @return int
     */
    public function complete($argv = [])
    {
        try {
            $goal = $this->promptForGoal($argv, "\nWhich goal would you like to mark complete? ");
        } catch (GoalNotFoundException $e) {
            print $e->getMessage() . "\n";
            return 1;
        }

        $this->greg->markComplete($goal);
        print "Marked the following goal as complete:\n";
        print $this->greg->goalToString($goal, "☑ ");

        return 0;
    }

    /**
     * Add progress to a goal
     *
     * @param array $argv
     * @return int
     */
    public function progress($argv = [])
    {
        try {
            $goal = $this->promptForGoal($argv, "\nFor which goal would you like to add progress? ");
        } catch (GoalNotFoundException $e) {
            print $e->getMessage() . "\n";
            return 1;
        }

        print $this->greg->goalToString($goal, "% ");

        $pstatus = $this->promptForProgressStatus();
        print $pstatus . " -- got it.\n";

        $notes = $this->promptForNotes();

        $progress_record = [
            'datetime' => date('Y-m-d H:i:s'),
            'status' => $pstatus,
            'notes' => $notes,
        ];
        $this->greg->addProgress($goal, $progress_record);
        print "Progress recorded.\n";

        return 0;
    }

    /**
     * Get detail of a goal
     *
     * @param array $argv
     * @return int
     */
    public function detail($argv = [])
    {
        try {
            $goal = $this->promptForGoal($argv, "\nSelect goal: ");
        } catch (GoalNotFoundException $e) {
            print $e->getMessage() . "\n";
            return 1;
        }

        print $this->greg->goalToString($goal, "% ");
        if (isset($goal->progress)) {
            print "Progress\n";
            print "--------\n";
            foreach ($goal->progress as $progress) {
                printf("* %s : %s\n  %s\n", $progress->datetime, $progress->status, $progress->notes);
            }
        } else {
            print "No progress recorded.\n";
        }

        return 0;
    }

    /**
     * Prompt to get a goal by index or id
     *
     * @param array $argv Input from command
     * @param string $text Message of the prompt
     * @return object Goal object
     */
    private function promptForGoal($argv = [], $text = "Which goal would you like to select? ")
    {
        $goals = $this->greg->getActiveGoals();

        if (!isset($argv[2])) {
            print "G O A L S\n";
            foreach ($goals as $i => $goal) {
                print $this->greg->goalToString($goal, $i + 1 . ". ");
            }
            print $text;
            $input = rtrim(fgets(STDIN));
            print "\n";
        } else {
            $input = trim($argv[2]);
        }

        $goal = $this->greg->getGoalById($input);
        if (!$goal) {
            $error = "No goal found for input '$input'";
            throw new GoalNotFoundException($error);
        }

        return $goal;
    }

    /**
     * Prompt for progress status
     *
     * @param string $text
     * @return string
     */
    private function promptForProgressStatus()
    {
        $statuses = Greg::$progress_statuses;

        $valid_responses = [];
        $prompt_pstatus = [];
        foreach ($statuses as $key => $status) {
            $prompt_pstatus[] = ($key) . ': ' . $status;
            $valid_responses[] = $key;
        }

        do {
            $prompt = "How are you progressing in this goal? (" . implode(' ', $prompt_pstatus) . ")? ";
            print $prompt;

            $input = strtolower(rtrim(fgets(STDIN)));
            if ($input == 'q') {
                return false;
            }

            if (in_array($input, $valid_responses)) {
                return Greg::$progress_statuses[$input];
            }

            print "Invalid response '" . $input . "' (use 'q' to cancel)\n";
        } while (!in_array($input, $valid_responses));
    }

    /**
     * Prompt for notes
     *
     * @param string $text
     * @return string
     */
    private function promptForNotes($text = "Add notes for your progress")
    {
        print "$text:\n> ";
        $input = rtrim(fgets(STDIN));

        return $input;
    }

    /**
     * help
     *
     * @return int
     */
    public function help()
    {
        print "Greg Regularly Evokes Goals\n";
        print " ▄▄  ▄▄▄   ▄▄   ▄▄\n";
        print "█  █ █  ▀ █▄▄█ █  █\n";
        print "▀▄▄█ █    ▀▄▄▄ ▀▄▄█\n";
        print "▄▄▄▀           ▄▄▄▀\n";
        print "\n";
        print "Usage:\n";
        print "  greg [options] <cmd> [arguments]\n";
        print "Options:\n";
        print "  -t --table     Show list of goals in table\n";
        print "Commands:\n";
        print "  help           Show this help message\n";
        print "  list           Show list of goals\n";
        print "  add <goal>     Add a new goal\n";
        print "  remind         Show goal reminder for today\n";
        print "  progress [id]  Mark your progress for a goal\n";
        print "  detail [id]    Show progress detail for a goal\n";
        print "  complete [id]  Mark a goal as complete\n";
        print "  version        Show version of greg\n";

        return 0;
    }
}
