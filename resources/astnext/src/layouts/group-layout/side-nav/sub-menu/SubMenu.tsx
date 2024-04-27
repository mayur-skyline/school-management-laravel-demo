import './SubMenu.scss';

import React, { ReactElement } from 'react';
import { Link, useLocation } from 'react-router-dom';

import Icon, { IconNames } from '../../../../components/base/icon/Icon';
import { STORE_ACTIONS } from '../../../../context/store';
import { useStore } from '../../../../hooks/useStore';

type SubMenu = React.HTMLAttributes<HTMLElement> & {
  link: string;
  icon: IconNames;
  activeIcon?: IconNames;
  contentClass?: string;
};

const SubMenu: React.FC<SubMenu> = ({
  className,
  contentClass,
  children,
  icon,
  // activeIcon,
  link,
}): ReactElement => {
  const { pathname } = useLocation();
  const isActive = pathname.includes(link);
  const [{ isSidenavOpen }, dispatch] = useStore();

  function toggleSidenav() {
    dispatch({ type: STORE_ACTIONS.toggleSideNav, payload: !isSidenavOpen });
  }

  return (
    <Link className={`w-full ${className}`} to={`/${link}`} onClick={toggleSidenav}>
      <div
        className={`menu-item mb-4 p-2 rounded-lg ${
          isActive ? 'bg-white border border-[#b0aacb21]' : ''
        }`}
      >
        <div className="menu-body flex items-center">
          <Icon icon={icon} className="mr-5" />
          <div className={`sub-nav-text manrope-normal-black-14px ${contentClass}`}>{children}</div>
        </div>
      </div>
    </Link>
  );
};

export default SubMenu;
