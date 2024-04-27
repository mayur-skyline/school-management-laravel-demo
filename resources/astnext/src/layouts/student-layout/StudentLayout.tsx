import React, { useRef, useState } from 'react';
import { useIdleTimer } from 'react-idle-timer';

import IdleTimeOutModal from '../../components/idle-timeout-modal/IdleTimeOutModal';
import { useLogout } from '../../hooks/useLogout';

type StudentLayout = React.HTMLAttributes<HTMLElement>;

const StudentLayout: React.FC<StudentLayout> = ({ children }) => {
  const [isIdleModalShown, setIsIdleModalShown] = useState(false);
  const { loading, logout } = useLogout();
  const idleTimer = useRef(null);
  const sessionTimeOut = useRef(null);

  const handleLogout = () => {
    setIsIdleModalShown(false);
    clearTimeout(sessionTimeOut.current);
    logout();
  };

  useIdleTimer({
    timeout: 1000 * 60 * 5,
    ref: idleTimer,
    onIdle() {
      setIsIdleModalShown(true);
      sessionTimeOut.current = setTimeout(handleLogout, 1000 * 30);
    },
    onAction() {
      clearTimeout(sessionTimeOut.current);
    },
    onActive() {
      clearTimeout(sessionTimeOut.current);
    },
  });

  return (
    <div className="student-layout">
      <IdleTimeOutModal
        open={isIdleModalShown}
        onClose={() => {
          setIsIdleModalShown(false);
        }}
        stay={() => {
          setIsIdleModalShown(false);
        }}
        logout={handleLogout}
        loading={loading}
      />
      {children}
    </div>
  );
};

export default StudentLayout;
