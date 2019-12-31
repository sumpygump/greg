<?php

namespace Greg;

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
    public function list()
    {
        $goals = $this->greg->getActiveGoals();
        if (empty($goals)) {
            print "No goals set. Set one with `greg add <goal>`\n";
            return 1;
        }

        foreach ($goals as $i => $goal) {
            print $this->greg->goalToString($goal, $i+1 . ". ");
        }

        return 0;
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
        $goals = $this->greg->getActiveGoals();
        if (!isset($argv[2])) {
            foreach ($goals as $i => $goal) {
                print $this->greg->goalToString($goal, $i + 1 . ". ");
            }
            print "Which goal would you like to mark complete? ";
            $input = rtrim(fgets(STDIN));
        } else {
            $input = trim($argv[2]);
        }

        $goal = $this->greg->getGoalById($input);
        if (!$goal) {
            print "No goal found for input '$input'\n";
            return 0;
        }

        $this->greg->markComplete($goal);
        print "Marked the following goal as complete:\n";
        print $this->greg->goalToString($goal, "☑ ");

        return 0;
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
        print "  greg <cmd> [arguments]\n";
        print "Commands:\n";
        print "  help        Show this help message\n";
        print "  list        Show list of goals\n";
        print "  add <goal>  Add a new goal\n";
        print "  remind      Show goal reminder for today\n";
        print "  complete    Mark a goal as complete\n";
        print "  version     Show version of greg\n";

        return 0;
    }
}
