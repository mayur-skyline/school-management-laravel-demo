import '@astnext/services/sentry.service';
import './i18n';

import { StrictMode } from 'react';
import { render } from 'react-dom';
import { BrowserRouter } from 'react-router-dom';
import urlJoin from 'url-join';

import { Providers } from './context';
import AppRouter from './router/AppRouter';
import ScrollToTop from './router/components/ScrollToTop';

const Index = () => {
  const url = urlJoin(process.env.MIX_ASTNEXT_APP_URL || '', '/ast-next');
  const pathname = new URL(url).pathname;

  return (
    <StrictMode>
      <BrowserRouter basename={pathname}>
        <Providers>
          <ScrollToTop />
          <AppRouter />
        </Providers>
      </BrowserRouter>
    </StrictMode>
  );
};

export default Index;

render(<Index />, document.getElementById('app'));
