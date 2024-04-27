import './StudentHome.scss';

import { TabContext, TabPanel as MaterialTabPanel } from '@mui/lab';
import { TabPanelProps as MaterialTabPanelProps } from '@mui/lab/TabPanel/TabPanel';
import { Fade } from '@mui/material';
import React, { useState } from 'react';

import useLoggedStudent from '../../hooks/useLoggedStudent';
import AssessmentFlow from '../_common/assessment-flow/AssessmentFlow';
import GreetStudent from './steps/GreetStudent';

type StudentHome = React.HTMLAttributes<HTMLElement>;

const ASSESSMENT_STEPS = ['GREET_STUDENT', 'ASSESSMENT_TAKEN', 'ASSESSMENT_FLOW'] as const;

export type AssessmentStep = (typeof ASSESSMENT_STEPS)[number];

type TabPanelProps = MaterialTabPanelProps & {
  value: AssessmentStep;
};

const TabPanel: React.FC<TabPanelProps> = ({ children, ...props }) => {
  return (
    <MaterialTabPanel {...props} sx={{ p: 0 }}>
      <Fade in timeout={700}>
        <div className="w-full h-full">{children as React.ReactElement}</div>
      </Fade>
    </MaterialTabPanel>
  );
};

const StudentHome: React.FC<StudentHome> = () => {
  const [step, setStep] = useState<AssessmentStep>('GREET_STUDENT');

  const { student } = useLoggedStudent();

  return (
    <div className="student-home xl:container mx-auto xl:px-10">
      <TabContext value={step}>
        <TabPanel value="GREET_STUDENT">
          <GreetStudent student={student} onNext={() => setStep('ASSESSMENT_FLOW')} />
        </TabPanel>

        <TabPanel value="ASSESSMENT_FLOW">
          <div className="assessment-trial xl:container mx-auto xl:px-10">
            <Fade in timeout={700}>
              <div className="h-full w-full">
                <AssessmentFlow
                  saveAnswers
                  student={student}
                  onExit={() => setStep('GREET_STUDENT')}
                />
              </div>
            </Fade>
          </div>
        </TabPanel>
      </TabContext>
    </div>
  );
};

export default StudentHome;
