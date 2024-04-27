import './Sidenav.scss';

import Box from '@mui/material/Box';
import Drawer from '@mui/material/Drawer';
import * as React from 'react';
import { useEffect, useRef, useState } from 'react';

import steerLogo from '../../../assets/images/steer-logo.svg';
import { STORE_ACTIONS } from '../../../context/store';
import { useIsDesktop } from '../../../hooks/useIsDesktop';
import { useStore } from '../../../hooks/useStore';
import groupRoutes from '../../../router/groupRoutes';
import MenuItem from './menu-item/MenuItem';

type SidenavProps = React.HTMLAttributes<HTMLElement> & {
  open: boolean;
};

const Sidenav: React.FC<SidenavProps> = ({ open }) => {
  const isDesktop = useIsDesktop();
  const sidenavRef = useRef(null);
  const [isScrollable, setIsScrollable] = useState(false);

  const [{ isSidenavOpen, session }, dispatch] = useStore();

  const { level } = session.user;

  function toggleSidenav() {
    dispatch({ type: STORE_ACTIONS.toggleSideNav, payload: !isSidenavOpen });
  }

  useEffect(() => {
    const handleResize = () => {
      setTimeout(() => {
        if (sidenavRef && sidenavRef.current) {
          setIsScrollable(sidenavRef.current.scrollHeight > sidenavRef.current.clientHeight);
        }
      }, 10);
    };

    window.addEventListener('resize', handleResize);
    handleResize();
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  const SidenavMenu = () => (
    <div className="nav-menu flex flex-col">
      <div className="nav-logo">
        <img src={steerLogo} alt="" />
      </div>
      <div
        ref={sidenavRef}
        className={`overflow-y-auto custom-scroller ${isScrollable ? '-mr-6' : ''}`}
      >
        <div className="w-full h-full flex flex-col">
          {groupRoutes.map(
            (route, key) =>
              (!route.levels || route.levels.includes(level)) && (
                <MenuItem key={key} route={route}>
                  {route.title}
                </MenuItem>
              )
          )}
        </div>
      </div>
    </div>
  );

  return (
    <Box component="nav" className="group-sidenav">
      {isDesktop ? (
        <SidenavMenu />
      ) : (
        <Drawer
          variant="temporary"
          open={open}
          onClose={toggleSidenav}
          ModalProps={{
            keepMounted: true,
          }}
        >
          <SidenavMenu />
        </Drawer>
      )}
    </Box>
  );
};

export default Sidenav;
