import { DAILY_CHECKIN_SYMPTOM_VALUES  } from '@/lib/types';
import type {DailyCheckinSymptomValue} from '@/lib/types';

export { DAILY_CHECKIN_SYMPTOM_VALUES };

export function isDailyCheckinSymptomValue(
    value: string,
): value is DailyCheckinSymptomValue {
    return (DAILY_CHECKIN_SYMPTOM_VALUES as readonly string[]).includes(value);
}
