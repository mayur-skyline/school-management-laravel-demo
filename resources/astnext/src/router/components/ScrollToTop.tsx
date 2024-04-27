import React from 'react';
import { useLocation } from 'react-router-dom';

type ScrollToTop = React.HTMLAttributes<HTMLElement>;

function ScrollToTop() {
  const location = useLocation();

  React.useEffect(() => {
    window.scrollTo(0, 0);
  }, [location]);

  return null;
}

export default ScrollToTop;
