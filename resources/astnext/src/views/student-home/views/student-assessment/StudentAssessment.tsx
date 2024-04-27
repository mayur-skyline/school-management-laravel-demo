import { Fade } from '@mui/material';
import React from 'react';
import { useNavigate } from 'react-router-dom';

import Student from '../../../../common/models/Student';
import { useStore } from '../../../../hooks/useStore';
import AssessmentFlow from '../../../_common/assessment-flow/AssessmentFlow';

const StudentAssessment = () => {
  const [{ session }] = useStore();
  const student = new Student(session.user);

  const navigate = useNavigate();

  function handleBack() {
    navigate('/student-home');
  }

  return (
    <div className="assessment-trial xl:container mx-auto xl:px-10">
      <Fade in timeout={700}>
        <div className="h-full w-full">
          <AssessmentFlow saveAnswers student={student} onExit={handleBack} />
        </div>
      </Fade>
    </div>
  );
};

export default StudentAssessment;
