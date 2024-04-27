<?php

namespace App\Util\Grouping\Composite;
use App\Models\Dbschools\Model_population;
use App\Models\Dbschools\Model_report_actionplan;
use App\Services\ActionPlanMetaServiceProvider;
use App\Services\ActionPlanServiceProvider;
use App\Util\Builder\Cohort\CohortBuilder;

abstract class CompositeBase
{
    protected $actionPlan;
    protected $population;
    protected $actionPlanService;

    public function __construct()
    {
        $this->actionPlan = new Model_report_actionplan();
        $this->population = new Model_population();
        $this->actionPlanMeta = new ActionPlanMetaServiceProvider();
        $this->actionPlanService = new ActionPlanServiceProvider();
        $this->cohortBuilder = new CohortBuilder();
    }

    abstract public function compositeList(): Array;

    abstract public function studentCompositeList(array $data): Array;

    abstract public function buildComposite(array $data): Array;

    abstract public function StudentCompositeRisksObject( object $data, string $rawdata, string $assessment_type, $risks ): array;

    abstract public function StudentHasCompositeRisksObject(object $data, string $rawdata, string $assessment_type): bool;

    abstract public function StudentSCICompositeRisksObject( object $data_in_school, object $data_out_of_school, 
                                                             string $in_school_rawdata, string $out_of_school_rawdata,
                                                             array $risks ): array;

}

