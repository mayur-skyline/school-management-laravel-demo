import { FC } from 'react';

import { DataRow } from '../../../../common/models/PermissionModal';
import AmberButton from '../../../../components/base/button/AmberButton';

export enum ActionKind {
  Rclick = 'RADIOCLICK',
  Cclick = 'CHECKBOXCLICK',
  AllClick = 'ALLCLICK',
  Ccampus5 = 'CHECKBOXCLICKFORLEVEL5',
  Save = 'SAVE',
  Load = 'LOAD',
  Level5 = 'LOADATLEVEL5',
  Allc5 = 'ALLCAMPUSESLEVEL5',
  Rcampus5 = 'RADIOCAMPUSLEVEL5',
  Refrash = 'FORLEVEL5TOLEVEL4',
  Allcampus = 'ALLCAMPUSCLICK',
}

interface permAction {
  type: ActionKind;
  startData?: { data?: DataRow[]; yearArr?: number[]; campus: string[]; house?: string[] };
  rId?: number;
  cId?: number;
  arrType?: number;
}

export interface permState {
  row: DataRow;
  all?: boolean[];
  campus: boolean[];
  year?: boolean[];
  house?: boolean[];
  allCampus?: boolean;
  errAt?: {
    campus: boolean;
    year: boolean;
    house: boolean;
  };
}

export const atStartRow: permState[] = [
  {
    row: {
      staff_name: '',
      staff_id: null,
      get_yrs: [''],
      get_cs: [''],
      get_hs: [''],
      get_set: '',
    },
    all: [],
    campus: [],
    year: [],
    house: [],
    errAt: {
      campus: false,
      year: false,
      house: false,
    },
  },
];

export function reducer(state: permState[], action: permAction) {
  const { type, startData, rId, cId, arrType } = action;
  const allT = Array(5).fill(false),
    yearT = Array(11).fill(false);

  switch (type) {
    case ActionKind.Refrash:
      return atStartRow;
    case ActionKind.Cclick:
      switch (arrType) {
        case 0: {
          const yearA = state[rId].year.map((ele, i) => (cId === i ? !ele : ele));
          const errAtYear = !yearA.reduce((prev, cur) => prev || cur);

          const houseErr = state[rId].house.some((el) => el);
          const campusErr = !state[rId].campus.reduce((prev, cur) => prev || cur);

          if (state[rId].all[0]) {
            state[rId] = {
              ...state[rId],
              year: yearA,
              errAt:
                errAtYear && houseErr && campusErr
                  ? {
                      year: false,
                      house: false,
                      campus: false,
                    }
                  : {
                      ...state[rId].errAt,
                      year: errAtYear,
                      house: houseErr,
                      campus: campusErr,
                    },
            };
          } else {
            state[rId] = {
              ...state[rId],
              year: yearA,
              errAt: { ...state[rId].errAt, year: errAtYear },
            };
          }
          break;
        }
        case 1: {
          const campusA = state[rId].campus.map((ele, i) => (cId === i ? !ele : ele));
          const errAtCampus = !campusA.reduce((prev, cur) => prev || cur);
          const houseErr = state[rId].house.some((el) => el);
          const yearErr = !state[rId].year.reduce((prev, cur) => prev || cur);
          if (state[rId].all[0]) {
            state[rId] = {
              ...state[rId],
              campus: campusA,
              errAt:
                errAtCampus && houseErr && yearErr
                  ? {
                      year: false,
                      house: false,
                      campus: false,
                    }
                  : {
                      ...state[rId].errAt,
                      campus: errAtCampus,
                      house: houseErr,
                      year: yearErr,
                    },
            };
          } else {
            state[rId] = {
              ...state[rId],
              campus: campusA,
              errAt: { ...state[rId].errAt, campus: errAtCampus },
            };
          }
          break;
        }
        case 2: {
          const houseA = state[rId].house.map((ele, i) => (cId === i ? !ele : ele));
          const errAtHouse = !houseA.reduce((prev, cur) => prev || cur);
          const yearErr = !state[rId].year.reduce((prev, cur) => prev || cur);
          const campusErr = !state[rId].campus.reduce((prev, cur) => prev || cur);
          if (state[rId].all[0]) {
            state[rId] = {
              ...state[rId],
              house: houseA,
              errAt:
                errAtHouse && campusErr && yearErr
                  ? {
                      year: false,
                      house: false,
                      campus: false,
                    }
                  : {
                      ...state[rId].errAt,
                      house: errAtHouse,
                      year: yearErr,
                      campus: campusErr,
                    },
            };
          } else {
            state[rId] = {
              ...state[rId],
              house: houseA,
              errAt: { ...state[rId].errAt, house: errAtHouse },
            };
          }
        }
      }
      return [...state];
    case ActionKind.Ccampus5: {
      const campusA = state[rId].campus.map((ele, i) => (cId === i ? !ele : ele));
      const allCampus = campusA.reduce((prev, cur) => prev && cur);
      state[rId] = {
        ...state[rId],
        allCampus: allCampus,
        campus: campusA,
      };
      return [...state];
    }
    case ActionKind.Rclick: {
      const campusT = Array(startData.campus.length).fill(false),
        houseT = Array(startData.house.length).fill(false);
      allT[cId] = true;
      switch (cId) {
        case 0:
          state[rId].campus = campusT;
          state[rId].errAt = {
            ...state[rId].errAt,
            campus: false,
            house: false,
            year: false,
          };
          break;
        case 1:
          yearT.forEach((ele, i) => {
            yearT[i] = true;
          });
          houseT.forEach((ele, i) => {
            houseT[i] = true;
          });
          state[rId].errAt = {
            ...state[rId].errAt,
            house: false,
            year: false,
            campus: [...state[rId].campus].every((ele) => !ele),
          };
          break;
        case 2:
          yearT.forEach((ele, i) => {
            yearT[i] = true;
          });

          state[rId].errAt = {
            ...state[rId].errAt,
            house: true,
            year: false,
            campus: [...state[rId].campus].every((ele) => !ele),
          };
          break;
        case 3:
          houseT.forEach((ele, i) => {
            houseT[i] = true;
          });

          state[rId].errAt = {
            ...state[rId].errAt,
            house: false,
            year: true,
            campus: state[rId].campus.every((el) => !el),
          };
          break;
      }
      state[rId] =
        cId === 4
          ? {
              ...state[rId],
              all: allT,
              errAt: {
                house: state[rId].house.every((el) => !el),
                year: !state[rId].year.reduce((prev, cur) => prev || cur),
                campus: !state[rId].campus.reduce((prev, cur) => prev || cur),
              },
            }
          : {
              ...state[rId],
              all: allT,
              year: yearT,
              house: houseT,
            };
      return [...state];
    }
    case ActionKind.Rcampus5:
      state[rId] = {
        ...state[rId],
        allCampus: true,
        campus: state[rId].campus.map((ele) => ele || true),
      };
      return [...state];

    case ActionKind.AllClick: {
      const campusT = Array(startData.campus.length).fill(false),
        houseT = Array(startData.house.length).fill(false);
      state = state.map((ele) => {
        allT[cId] = true;
        switch (cId) {
          case 0:
            ele.errAt = {
              ...ele.errAt,
              campus: false,
              house: false,
              year: false,
            };
            break;
          case 1:
            yearT.forEach((ele, i) => {
              yearT[i] = true;
            });
            houseT.forEach((ele, i) => {
              houseT[i] = true;
            });
            ele.errAt = {
              ...ele.errAt,
              house: false,
              year: false,
              campus: !ele.campus.reduce((prev, cur) => prev || cur),
            };
            break;
          case 2:
            yearT.forEach((ele, i) => {
              yearT[i] = true;
            });
            ele.errAt = {
              ...ele.errAt,
              house: true,
              year: false,
              campus: !ele.campus.reduce((prev, cur) => prev || cur),
            };
            break;
          case 3:
            houseT.forEach((ele, i) => {
              houseT[i] = true;
            });
            ele.errAt = {
              ...ele.errAt,
              house: false,
              year: true,
              campus: !ele.campus.reduce((prev, cur) => prev || cur),
            };
            break;
          case 4:
            ele.errAt = {
              ...ele.errAt,
              house: ele.house.some((el) => el),
              year: !ele.year.reduce((prev, cur) => prev || cur),
              campus: !ele.campus.reduce((prev, cur) => prev || cur),
            };
            return {
              ...ele,
              all: allT,
            };
        }
        return {
          ...ele,
          all: allT,
          campus: campusT,
          year: yearT,
          house: houseT,
        };
      });
      return [...state];
    }
    case ActionKind.Allcampus:
      state = state.map((ele) => {
        const campusA = ele.campus;
        campusA[cId] = rId === 0 ? false : true;
        const campusE = !campusA.reduce((prev, cur) => prev || cur);
        return {
          ...ele,
          campus: campusA,
          errAt: {
            ...ele.errAt,
            campus: campusE,
          },
        };
      });
      return [...state];

    case ActionKind.Allc5:
      state = state.map((ele) => {
        ele.campus.forEach((e, i) => {
          ele.campus[i] = true;
        });

        return {
          ...ele,
          allCampus: true,
          campus: ele.campus,
        };
      });
      return [...state];
    case ActionKind.Load:
      if (!startData.data) {
        return atStartRow;
      }
      state = startData.data.map((data) => {
        const allT = Array(5).fill(false),
          campusT = Array(startData.campus.length).fill(false),
          yearT = Array(startData.yearArr.length).fill(false),
          houseT = Array(startData.house.length).fill(false);
        switch (data.get_set) {
          case 'all':
            allT[1] = true;
            yearT.forEach((ele, i) => {
              yearT[i] = true;
            });

            houseT.forEach((ele, i) => {
              houseT[i] = true;
            });
            data.get_cs.forEach((ele) => {
              campusT[startData.campus.indexOf(ele)] = true;
            });
            break;
          case 'year':
            allT[2] = true;

            yearT.forEach((ele, i) => {
              yearT[i] = true;
            });
            data.get_hs.forEach((ele) => {
              houseT[startData.house.indexOf(ele)] = true;
            });
            data.get_cs.forEach((ele) => {
              campusT[startData.campus.indexOf(ele)] = true;
            });
            break;
          case 'hs':
            allT[3] = true;

            houseT.forEach((ele, i) => {
              houseT[i] = true;
            });
            data.get_yrs.forEach((ele) => {
              yearT[startData.yearArr.indexOf(+ele)] = true;
            });
            data.get_cs.forEach((ele) => {
              campusT[startData.campus.indexOf(ele)] = true;
            });
            break;
          case 'cs':
            data.get_cs.forEach((ele) => {
              campusT[startData.campus.indexOf(ele)] = true;
            });
            data.get_hs.forEach((ele) => {
              houseT[startData.house.indexOf(ele)] = true;
            });
            data.get_yrs.forEach((ele) => {
              yearT[startData.yearArr.indexOf(+ele)] = true;
            });
            break;
          case 'none':
          case 'training':
            allT[0] = true;
          // eslint-disable-next-line no-fallthrough
          case 'custom':
            if (allT[0]) {
              allT[0] = true;
            } else {
              allT[4] = true;
            }
            data.get_yrs.forEach((ele) => {
              yearT[startData.yearArr.indexOf(+ele)] = true;
            });
            data.get_cs.forEach((ele) => {
              campusT[startData.campus.indexOf(ele)] = true;
            });
            data.get_hs.forEach((ele) => {
              houseT[startData.house.indexOf(ele)] = true;
            });
        }
        return {
          row: data,
          all: allT,
          campus: campusT,
          year: yearT,
          house: houseT,
          errAt: {
            campus: false,
            house: false,
            year: false,
          },
        };
      });

      return [...state];
    case ActionKind.Level5:
      state = startData.data.map((data) => {
        const campusT = Array(startData.campus.length).fill(false);
        let campusA = false;
        if (data.get_set === 'cs') {
          campusA = true;
          for (let i = 0; i < data.get_cs.length; i++) {
            campusT[i] = true;
          }
        } else {
          data.get_cs.forEach((ele) => {
            campusT[startData.campus.indexOf(ele)] = true;
          });
        }

        return {
          row: data,
          allCampus: campusA,
          campus: campusT,
        };
      });

      return [...state];

    default:
      return state;
  }
}
export const AmberButtonPermission: FC<{ title: string; onClick?: () => void }> = (props) => {
  return (
    <AmberButton
      className="shadow-lg"
      style={{
        height: '38px',
        margin: '0 16px 0 0',
        boxShadow: '0 10px 15px -3px rgb(0 0 0 / 10%)',
      }}
      onClick={props.onClick}
    >
      <p className="font-bold py-1 px-4">{props.title}</p>
    </AmberButton>
  );
};
