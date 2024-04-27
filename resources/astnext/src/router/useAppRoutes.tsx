import Badge from '@astnext/components/base/badge/Badge';
import { useIsOldSchoolSetupEnabled } from '@astnext/hooks/data/useIsOldSchoolSetupEnabled';
import React, { useMemo } from 'react';

import { type Package, type SessionUser } from '../common/models/Session';
import Icon, { type IconNames } from '../components/base/icon/Icon';

export type Route = {
  link: string;
  pathname: string;
  icon: IconNames;
  activeIcon: IconNames;
  title: string;
  animated?: boolean;
  separator?: boolean;
  levels?: SessionUser['level'][];
  packages?: Package[];
  after?: React.ReactNode;
};

export const useAppRoutes = () => {
  const enableOldSchoolSetup = useIsOldSchoolSetupEnabled();

  const routes: Route[] = useMemo(
    () => [
      {
        link: '/tracking',
        pathname: '/tracking',
        icon: 'tracking',
        activeIcon: 'tracking-active',
        title: 'Tracking',
        animated: true,
        levels: [4, 5, 6, 7],
      },
      {
        link: '/school-actions',
        pathname: '/school-actions/:page',
        icon: 'school-actions',
        activeIcon: 'school-actions-active',
        title: 'School Actions',
        animated: true,
        levels: [4, 5, 6, 7],
      },
      {
        link: '/safeguarding',
        pathname: '/safeguarding',
        icon: 'safeguarding',
        activeIcon: 'safeguarding-active',
        title: 'Safeguarding',
        animated: true,
        packages: ['safeguarding'],
        levels: [4, 5, 6, 7],
      },
      {
        link: '/school-impact',
        pathname: '/school-impact/:page',
        icon: 'school-impact',
        activeIcon: 'school-impact-active',
        title: 'School Impact',
        animated: true,
        packages: ['impact'],
        levels: [4, 5, 6, 7],
      },
      {
        link: '/pshe',
        pathname: '/pshe',
        icon: 'curriculums',
        activeIcon: 'curriculums-active',
        title: 'PSHE',
        animated: true,
        separator: true,
        packages: ['pshe'],
        levels: [4, 5, 6, 7],
      },
      {
        link: '/assessment-tracker',
        pathname: '/assessment-tracker/:page',
        icon: 'assessment-tracker',
        activeIcon: 'assessment-tracker-active',
        title: 'Assessment Tracker',
      },
      {
        link: '/reports',
        pathname: '/reports',
        icon: 'export-data',
        activeIcon: 'export-data-active',
        title: 'Reports',
        levels: [4, 5, 6, 7],
      },
      {
        link: '/training',
        pathname: '/training',
        icon: 'training',
        activeIcon: 'training-active',
        title: 'Training',
      },
      {
        link: '/admin',
        pathname: '/admin/:page',
        icon: 'menu',
        activeIcon: 'menu-active',
        title: 'Admin',
        levels: [3, 5, 6],
        after: enableOldSchoolSetup ? (
          <Badge size="sm" className="!bg-[#FEF8E3] ml-2 !rounded-full">
            <div className="flex gap-[6px]">
              <Icon icon="calendar" size="8" />
              <p className="font-bold text-[10px] text-[#504F68]">Set-up</p>
            </div>
          </Badge>
        ) : (
          ''
        ),
      },
    ],
    []
  );

  return routes;
};
