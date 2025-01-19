<?php

namespace App\Console;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Office;
use Illuminate\Support\Facades\Schema;
use Slim\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Faker\Factory;
use Carbon\Carbon;

class PopulateDatabaseCommand extends Command
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('db:populate');
        $this->setDescription('Populate database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Populate database...');

        /** @var \Illuminate\Database\Capsule\Manager $db */
        $db = $this->app->getContainer()->get('db');

        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=0");
        $db->getConnection()->statement("TRUNCATE `employees`");
        $db->getConnection()->statement("TRUNCATE `offices`");
        $db->getConnection()->statement("TRUNCATE `companies`");
        $db->getConnection()->statement("SET FOREIGN_KEY_CHECKS=1");

        $faker = Factory::create();

        $companyCount = rand(2, 4);
        $companies = [];
        for ($i = 0; $i < $companyCount; $i++) {
            $companies[] = [
                'name' => $faker->company,
                'phone' => $faker->phoneNumber,
                'email' => $faker->companyEmail,
                'website' => $faker->url,
                'logo' => $faker->imageUrl(300, 300, 'business'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        foreach ($companies as $company) {
            $db->getConnection()->table('companies')->insert($company);
        }

        $offices = [];
        foreach ($companies as $company) {
            $officeCount = rand(2, 3);
            for ($i = 0; $i < $officeCount; $i++) {
                $offices[] = [
                    'name' => $faker->company,
                    'address' => $faker->address,
                    'city' => $faker->city,
                    'zip_code' => $faker->postcode,
                    'country' => $faker->country,
                    'email' => $faker->companyEmail,
                    'company_id' => $company['id'], // Relier le bureau à la société
                    'created_at' => Carbon::now(), // Utilisation de Carbon
                    'updated_at' => Carbon::now(), // Utilisation de Carbon
                ];
            }
        }

        foreach ($offices as $office) {
            $db->getConnection()->table('offices')->insert($office);
        }

        $employees = [];
        foreach ($companies as $company) {
            for ($i = 0; $i < 3; $i++) {
                $employees[] = [
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'company_id' => $company['id'],
                    'email' => $faker->email,
                    'job_title' => $faker->jobTitle,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        foreach ($employees as $employee) {
            $db->getConnection()->table('employees')->insert($employee);
        }

        $output->writeln('Database created successfully!');
        return 0;
    }
}
