<?php

namespace DjFerreira\Gcrud\Commands;

use DjFerreira\Gcrud\Core\Generator;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ModelGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gcrud:makemodel
    {name=name : Class (singular) for example User}
    {--table=default : Table name (plural) for example users | Default is generated-plural}
    {--timestamps=true : Set default timestamps}
    {--interactive=false : Interactive mode}
    {--all=false : Interactive mode}
    {--overwrite=true : If file exists, determine if overwrite}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create model with a multilayer structure';

    /**
     *
     * Generator support instance
     *
     * @var DjFerreira\Gcrud\Core\Generator
     */
    protected $generator;


    /**
     * The String support instance
     *
     * @var \Illuminate\Support\Str
     */
    protected $str;

    /**
     * Schema support instance
     *
     * @var \Illuminate\Support\Facades\Schema $schema
     */
    protected $schema;

    /**
     * Create a new command instance.
     *
     * @param Generator $generator
     * @param Str $str
     * @param Schema $schema
     */
    public function __construct(Generator $generator, Str $str, Schema $schema)
    {
        parent::__construct();
        $this->generator = $generator;
        $this->str       = $str;
        $this->schema    = $schema;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        // Checking interactive mode
        if ($this->option('interactive') == "") {
            $this->interactive();
            return 0;
        }

        // Checking all mode
        if ($this->option('all') == "") {
            $this->all();
            return 0;
        }

        // If here, no interactive || all selected
        $name = ucwords($this->argument('name'));
        $table = $this->option('table');
        $timestamps = ($this->option('timestamps') == 'false' ? false : true);

        $overwrite = ($this->option('overwrite') == 'false' ? false : true);

        $this->generate($name, $table, $timestamps, $overwrite);
        return 0;
    }

    /**
     * Handle all-db generation
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function all()
    {
        try {
            $tables =  \DB::connection()->getDoctrineSchemaManager()->listTableNames();
            $ignoreTables = ['migrations', 'failed_jobs', 'password_resets'];
            foreach ($tables as $table) {
                if (in_array($table, $ignoreTables)) {
                    continue;
                }
                $name = ucwords(str_replace('_', ' ', $table));
                $name = str_replace(' ', '', $name);
                $name = ucwords($this->str->singular($name));
                $columns = Schema::getColumnListing($table);
                in_array('created_at', $columns) ? $timestamps = true : $timestamps = false;
                $overwrite = ($this->option('overwrite') == 'false' ? false : true);

                $this->generate($name, 'default', $timestamps, $overwrite);
            }
        } catch (QueryException $exception) {
            $this->error("Error: " . $exception->getMessage());
        }
    }


    /**
     * Generate CRUD in interactive mode
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function interactive()
    {
        $this->info("Welcome in Interactive mode");

        $this->comment("This command will guide you through creating your Model");
        $name = $this->ask('What is name of your Model?');
        $name = ucwords($name);
        $table = $this->ask("Table name [" . strtolower($this->str->plural($name)) . "]:");
        if ($table == "") {
            $table = $this->str->plural($name);
        }
        $table = strtolower($table);
        $choice = $this->choice('Do your table has timestamps column?', ['No', 'Yes'], 0);
        $choice === "Yes" ? $timestamps = true : $timestamps = false;

        $confirmOverwrite = $this->ask("If the model file {$name} already exists, do you want it to be overwritten? [Y,n]") ?? 'y';

        $overwrite = true;
        if (strtolower($confirmOverwrite) === 'n') {
            $overwrite = false;
        } elseif (strtolower($confirmOverwrite) !== 'y') {
            $this->error("Aborted!");
            return;
        }

        $this->info("Please confim this data");
        $this->line("Name: $name");
        $this->line("Table: $table");
        $this->line("Timestamps: $choice");

        $confirm = $this->ask("Press y to confirm, type N to restart");
        if ($confirm == "y") {
            $this->generate($name, $table, $timestamps, $overwrite);
            return;
        }
        $this->error("Aborted!");
    }

    /**
     * Handle data generation
     * @param $name string Model Name
     * @param $table string Table Name
     * @param $timestamps boolean
     * @param $overwrite boolean
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function generate($name, $table, $timestamps, $overwrite)
    {
        $this->comment("Generating {$name} Model");
        $this->generator->model($name, $table, $timestamps, $overwrite);
        $this->info("Generated {$name} Model!");

        $scopeTraitGenerated = $this->generator->scopeTrait();
        if ($scopeTraitGenerated) {
            $this->comment("Generating ScopeTrait");
            $this->info("Generated ScopeTrait");
        }
    }
}
