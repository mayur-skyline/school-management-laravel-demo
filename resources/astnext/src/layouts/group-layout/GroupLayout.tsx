import './GroupLayout.scss';

import Box from '@mui/material/Box';
import * as React from 'react';
import { useEffect } from 'react';

import { STORE_ACTIONS } from '../../context/store';
import { useIsDesktop } from '../../hooks/useIsDesktop';
import { useStore } from '../../hooks/useStore';
import SideBar from './side-nav/Sidenav';

type GroupLayoutProps = React.HTMLAttributes<HTMLElement>;

const GroupLayout: React.FC<GroupLayoutProps> = ({ children }) => {
  const isDesktop = useIsDesktop();
  const [{ isSidenavOpen }, dispatch] = useStore();

  function toggleSidenav(value = !isSidenavOpen) {
    dispatch({ type: STORE_ACTIONS.toggleSideNav, payload: value });
  }

  useEffect(() => {
    if (!isDesktop) {
      toggleSidenav(false);
    }
  }, [isDesktop]);

  return (
    <Box
      className="group-layout relative"
      style={isDesktop ? { width: 'calc(100% - 225px)', marginLeft: '225px' } : {}}
    >
      <SideBar open={isSidenavOpen} />
      {children}
    </Box>
  );
};

export default GroupLayout;
