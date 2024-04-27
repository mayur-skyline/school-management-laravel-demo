import partyLotte from '@astnext/assets/lotte-animations/party.json';
import { type FeedbackQuestionKey } from '@astnext/common/models/UserConfigs';
import Icon from '@astnext/components/base/icon/Icon';
import TabPanelBase from '@astnext/components/base/TabPanelBase';
import Image from '@astnext/components/Image';
import { useMixpanelTrack } from '@astnext/context/mixpanel/hooks/useMixpanelTrack';
import { useFeedbackQuestions } from '@astnext/hooks/data/useFeedbackQuestions';
import { type PopperPlacementType } from '@mui/base/PopperUnstyled/PopperUnstyled';
import CloseIcon from '@mui/icons-material/Close';
import { TabContext } from '@mui/lab';
import { Popper } from '@mui/material';
import IconButton from '@mui/material/IconButton';
import * as React from 'react';
import { useCallback, useEffect, useRef, useState } from 'react';
import Lottie from 'react-lottie';

type FeedbackPopup = {
  feedbackKey: FeedbackQuestionKey;
  feedbackName: string;
  question?: string;
  placement?: PopperPlacementType;
  fixed?: boolean;
  visibleIn?: number;
};

export const FEEDBACK_STEPS = ['home', 'liked', 'disliked', 'disabled-feedback'] as const;

export type FeedbackStep = (typeof FEEDBACK_STEPS)[number];

const TabPanel = TabPanelBase<FeedbackStep>;

const POPUP_TIMERS = {
  VISIBLE_IN: 3,
  INACTIVE_FOR: 20,
  CLOSES_AFTER_INPUT_IN: 3,
};

const FeedbackPopup: React.FC<FeedbackPopup> = ({
  feedbackKey,
  question,
  feedbackName,
  visibleIn = POPUP_TIMERS.VISIBLE_IN,
  placement = 'bottom',
  fixed,
}) => {
  const [anchorEl, setAnchorEl] = React.useState<HTMLButtonElement | null>(null);
  const childRef = useRef(null);
  const [opened, setOpened] = useState(false);
  const [step, setStep] = useState<FeedbackStep>('home');

  const track = useMixpanelTrack();

  const {
    shouldShowQuestion,
    addQuestionToHiddenList,
    disableFeedbackQuestions,
    hideThisQuestion,
    isUpdated,
  } = useFeedbackQuestions(feedbackKey);

  const onUserChoice = useCallback(
    (choice: Omit<FeedbackStep, 'home'> | 'closed-without-feedback', callback: any) => {
      track('user-feedback', {
        type: feedbackName,
        choice,
      });

      if (typeof callback === 'function') {
        callback();
      }
    },
    [feedbackName, track]
  );

  const handleOpenPopup = (event: React.MouseEvent<any>) => {
    if (!opened && shouldShowQuestion) {
      setAnchorEl(event.currentTarget);
      setOpened(true);
    }
  };

  const handleClosePopup = useCallback(() => {
    setAnchorEl(null);
    hideThisQuestion();
  }, [hideThisQuestion]);

  useEffect(() => {
    if (!shouldShowQuestion) {
      return;
    }

    const timeout = setTimeout(() => {
      childRef?.current?.click();
    }, visibleIn * 1000);

    return () => {
      clearTimeout(timeout);
    };
  }, [opened, shouldShowQuestion, visibleIn]);

  useEffect(() => {
    if (opened && shouldShowQuestion) {
      const timeout = setTimeout(() => {
        onUserChoice('closed-without-feedback', () => {
          handleClosePopup();
          addQuestionToHiddenList();
        });
      }, POPUP_TIMERS.INACTIVE_FOR * 1000);

      return () => {
        clearTimeout(timeout);
      };
    }
  }, [addQuestionToHiddenList, handleClosePopup, onUserChoice, opened, shouldShowQuestion]);

  useEffect(() => {
    if (opened && shouldShowQuestion && isUpdated) {
      const timeout = setTimeout(() => {
        handleClosePopup();
      }, POPUP_TIMERS.CLOSES_AFTER_INPUT_IN * 1000);

      return () => {
        clearTimeout(timeout);
      };
    }
  }, [handleClosePopup, isUpdated, opened, shouldShowQuestion]);

  const open = Boolean(anchorEl);
  const id = open ? 'simple-popover' : undefined;

  return (
    <div>
      <div role="button" ref={childRef} onClick={handleOpenPopup} />
      <Popper
        id={id}
        open={open}
        anchorEl={anchorEl}
        className={`!rounded-[14px] !shadow-[0_7px_16px_#00000019] !overflow-hidden bg-white z-10 ${
          fixed ? '!fixed' : ''
        }`}
        placement={placement}
      >
        <TabContext value={step}>
          <TabPanel value="home">
            <div className="p-[24px] w-[290px]">
              <div className="flex justify-end">
                <IconButton
                  aria-label="close"
                  onClick={() =>
                    onUserChoice('closed-without-feedback', () => {
                      handleClosePopup();
                      addQuestionToHiddenList();
                    })
                  }
                  sx={{
                    flexShrink: 0,
                    marginTop: '-20px',
                    marginRight: '-20px',
                    color: (theme) => theme.palette.grey[500],
                  }}
                >
                  <CloseIcon />
                </IconButton>
              </div>
              <p className="font-bold relative -top-[20px] w-[90%]">
                {question || `What do you think of the ${feedbackName}?`}
              </p>

              <div className="flex items-center gap-[11px] mb-[11px]">
                <button
                  className="bg-[#7EBE5C36] rounded-[4px] h-[31px] w-[120px]"
                  onClick={() => {
                    onUserChoice('liked', () => {
                      setStep('liked');
                      addQuestionToHiddenList();
                    });
                  }}
                >
                  <div className="w-full flex-center gap-[10px]">
                    <Icon icon="thumbs-up" size="12" />
                    <span>I Like it</span>
                  </div>
                </button>
                <button
                  className="bg-[#E2474036] rounded-[4px] h-[31px] w-[120px]"
                  onClick={() => {
                    onUserChoice('disliked', () => {
                      setStep('disliked');
                      addQuestionToHiddenList();
                    });
                  }}
                >
                  <div className="w-full flex-center gap-[10px]">
                    <Icon icon="thumbs-up" size="12" />
                    <span>Not for me!</span>
                  </div>
                </button>
              </div>

              <button
                className="text-[#504E6A] text-[12px]"
                onClick={() => {
                  onUserChoice('disabled-feedback', () => {
                    setStep('disabled-feedback');
                    disableFeedbackQuestions();
                  });
                }}
              >
                Switch off feedback questions
              </button>
            </div>
          </TabPanel>

          <TabPanel value="liked">
            <div className="bg-[#9DC69C] p-[24px] w-[290px]">
              <div className="flex justify-end">
                <IconButton
                  aria-label="close"
                  onClick={handleClosePopup}
                  sx={{
                    flexShrink: 0,
                    marginTop: '-20px',
                    marginRight: '-20px',
                    color: 'black',
                  }}
                >
                  <CloseIcon color="inherit" />
                </IconButton>
              </div>
              <div className="flex items-center relative -top-[10px] -left-[10px]">
                <Lottie
                  options={{
                    loop: true,
                    autoplay: true,
                    animationData: partyLotte,
                    rendererSettings: {
                      preserveAspectRatio: 'xMidYMid slice',
                    },
                  }}
                  width={110}
                  height={110}
                />
                <div className="flex-auto text-white">
                  <p className="text-[17px] font-bold">Great to hear!</p>
                  <p className="text-[12px]">Thanks for telling us</p>
                </div>
              </div>
            </div>
          </TabPanel>

          <TabPanel value="disliked">
            <div className="bg-[#DB7A7A] p-[24px] w-[290px]">
              <div className="flex justify-end">
                <IconButton
                  aria-label="close"
                  onClick={handleClosePopup}
                  sx={{
                    flexShrink: 0,
                    marginTop: '-20px',
                    marginRight: '-20px',
                    color: 'black',
                  }}
                >
                  <CloseIcon color="inherit" />
                </IconButton>
              </div>
              <div className="flex items-center relative gap-[10px] -top-[10px]">
                <Image
                  src="/public/astnext/images/disliked-feedback.svg"
                  className="w-[80px] h-[78px]"
                />
                <div className="flex-auto text-white">
                  <p className="text-[17px] font-bold">Understood!</p>
                  <p className="text-[12px]">Thanks for telling us</p>
                </div>
              </div>
            </div>
          </TabPanel>

          <TabPanel value="disabled-feedback">
            <div className="p-[24px] w-[290px]">
              <div className="flex justify-end">
                <IconButton
                  aria-label="close"
                  onClick={handleClosePopup}
                  sx={{
                    flexShrink: 0,
                    marginTop: '-20px',
                    marginRight: '-20px',
                    color: (theme) => theme.palette.grey[500],
                  }}
                >
                  <CloseIcon />
                </IconButton>
              </div>
              <div className="p-[20px] relative -top-[10px]">
                <p className="font-bold mb-2">Sorry to disturb!</p>
                <p>You won’t see any more of these questions</p>
              </div>
            </div>
          </TabPanel>
        </TabContext>
      </Popper>
    </div>
  );
};

export default FeedbackPopup;
