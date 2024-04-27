import React from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';

import type Student from '../../../common/models/Student';
import NextButton from '../../_common/assessment-flow/components/NextButton';
import introImage from '../assets/intro-image.svg';

type GreetStudent = {
  student: Student;
  loading?: boolean;
  onNext();
};

const GreetStudent: React.FC<GreetStudent> = ({ student, loading, onNext }) => {
  const { t } = useTranslation();

  return (
    <>
      <div className="min-h-screen flex flex-col items-center justify-center p-12">
        <img src={introImage} alt="hand-waved" className="mb-20" />
        <h1 className="text-[45px] font-bold mb-6">
          {t('hi')} {student?.firstname}!
        </h1>
        <h2 className="text-[22px] mb-10">{t('landing_welcome')}</h2>
        <NextButton loading={loading} onClick={onNext} className="mb-12" />

        {Boolean(student.ttw_status) && (
          <Link to="/student-home/take-the-wheel">
            <NextButton
              customTitle="Take the Wheel"
              style={{
                width: '200px',
                background: 'none',
                color: '#ec772f',
                border: '1px solid rgb(236, 119, 47)',
              }}
            />
          </Link>
        )}
      </div>
    </>
  );
};

export default GreetStudent;
