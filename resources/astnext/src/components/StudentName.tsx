import { type SharedStudent } from '@astnext/common/models/RoyalSpringboard';
import React, { useMemo } from 'react';
import { Link, useLocation } from 'react-router-dom';
import urlJoin from 'url-join';

import type Student from '../common/models/Student';
import { type IStudent } from '../common/models/Student';
import { useUserConfigs } from '../hooks/data/useUserConfigs';
import { useStore } from '../hooks/useStore';

type StudentName = React.HTMLAttributes<HTMLElement> & {
  student: IStudent | Student | SharedStudent;
  opensInNewTab?: boolean;
  disableNavigation?: boolean;
};

export const useShouldShowStudentNameCode = () => {
  const [{ session }] = useStore();

  const { pseudonymizing } = useUserConfigs();

  return useMemo(
    () => pseudonymizing || session.user.level === 6,
    [pseudonymizing, session.user.level]
  );
};

const StudentName: React.FC<StudentName> = ({
  student,
  disableNavigation = false,
  opensInNewTab,
  ...props
}) => {
  const location = useLocation();
  const shouldShowPseudoName = useShouldShowStudentNameCode();

  if (!student || !student.id) {
    return <></>;
  }

  const studentTrackingPath = `tracking/student/${student.id}?origin=${location.pathname}`;

  const Name = () => (
    <span className="student-name" {...props}>
      {shouldShowPseudoName ? student?.name_code || student?.name : student?.name}
    </span>
  );

  if (disableNavigation) {
    return <Name />;
  }

  if (opensInNewTab) {
    const url = urlJoin(process.env.MIX_ASTNEXT_APP_URL || '', `/ast-next/${studentTrackingPath}`);

    return (
      <a href={url} target="_blank" rel="noreferrer noopener">
        <Name />
      </a>
    );
  }

  return (
    <Link to={`/${studentTrackingPath}`}>
      <Name />
    </Link>
  );
};

export default StudentName;
