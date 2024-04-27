import { getEnvironment } from '@astnext/common/helpers';
import * as Sentry from '@sentry/react';

const environment = getEnvironment();

if (environment === 'DEV' || environment === 'MIRROR' || environment === 'PROD') {
  Sentry.init({
    dsn: 'https://39a0d1d61e0142c798754da1b405fd96@o4505152775061504.ingest.sentry.io/4505165100154880',
    integrations: [new Sentry.BrowserTracing(), new Sentry.Replay()],
    // Performance Monitoring
    tracesSampleRate: 1.0, // Capture 100% of the transactions, reduce in production!
    environment,
    // Session Replay
    replaysSessionSampleRate: 0.1, // This sets the sample rate at 10%. You may want to change it to 100% while in development and then sample at a lower rate in production.
    replaysOnErrorSampleRate: 1.0, // If you're not already sampling the entire session, change the sample rate to 100% when sampling sessions where errors occur.
  });
}
