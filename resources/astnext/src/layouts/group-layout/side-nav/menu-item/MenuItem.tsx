import './MenuItem.scss';

import React, { ReactElement } from 'react';
import { Link, matchPath, useLocation } from 'react-router-dom';

import Icon from '../../../../components/base/icon/Icon';
import Separator from '../../../../components/base/separator/Separator';
import HtmlTooltip from '../../../../components/base/tooltip/HtmlTooltip';
import { STORE_ACTIONS } from '../../../../context/store';
import { isProduction } from '../../../../hooks/useIsProduction';
import { useStore } from '../../../../hooks/useStore';
import { Route } from '../../../../router/groupRoutes';

type MenuItem = React.HTMLAttributes<HTMLElement> & {
  route: Route;
  contentClass?: string;
};

const MenuItem: React.FC<MenuItem> = ({
  route,
  className,
  contentClass,
  children,
}): ReactElement => {
  const { link, icon, activeIcon, separator, pathname, title } = route;

  const location = useLocation();
  const isActive = matchPath(pathname, location.pathname);
  const [{ isSidenavOpen }, dispatch] = useStore();

  function toggleSidenav() {
    dispatch({ type: STORE_ACTIONS.toggleSideNav, payload: !isSidenavOpen });
  }

  if (title === '' && isProduction()) {
    return (
      <>
        <HtmlTooltip
          placement="right"
          title={
            <React.Fragment>
              <p className="text-base font-bold mb-2">Coming Soon:</p>
              <p className="text-sm">This will be available on January 15th</p>
            </React.Fragment>
          }
        >
          <div
            className={`
                menu-item rounded-lg overflow-x-hidden text-gray-400 cursor-not-allowed
                flex items-center p-3 mb-3 transition-all -translate-x-0.5
                ${className} min-h-[47px] ml-3
              `}
          >
            <div className="inline">
              <Icon icon={icon} className="mr-3" />
            </div>
            <div className={`${contentClass}`}>{children}</div>
          </div>
        </HtmlTooltip>
        {separator && (
          <>
            <Separator />
            <hr className="divide" />
          </>
        )}
      </>
    );
  }

  return (
    <>
      <Link to={link} className="" onClick={toggleSidenav}>
        <div
          className={`
        menu-item rounded-lg hover:opacity-80 overflow-x-hidden
        flex items-center p-3 mb-3 m-3 transition-all -translate-x-0.5
        hover:translate-x-0 active:translate-x-0 hover:bg-white hover:text-[#514E6A]
        ${className}
        ${isActive ? 'bg-white  border border-[#b0aacb21] text-[#514E6A]' : 'text-gray-400'}`}
        >
          {isActive ? (
            <Icon icon={activeIcon} className="mr-3" />
          ) : (
            <>
              <div className="inline group-hover:hidden">
                <Icon icon={icon} className="mr-3" />
              </div>
              <div className="hidden group-hover:inline">
                <Icon icon={activeIcon} className="mr-5" />
              </div>
            </>
          )}
          <div className={`${contentClass}`}>{children}</div>
        </div>
      </Link>
      {separator && (
        <>
          <Separator className="mb-5 mt-10" />
          <hr className="divide" />
        </>
      )}
    </>
  );
};

export default MenuItem;
