define([], function () {
    const isWildcard = (value) => {
        return value === '*';
    };

    const isQuestionMark = (value) => {
        return value === '?';
    };

    const isInRange = (value, start, stop) => {
        return value >= start && value <= stop;
    };

    const isValidRange = (value, start, stop) => {
        const sides = value.split('-');
        switch (sides.length) {
            case 1:
                return isWildcard(value) || isInRange(parseInt(value, 10), start, stop);
            case 2:
                const [small, big] = sides.map((side) => parseInt(side, 10));
                return small <= big && isInRange(small, start, stop) && isInRange(big, start, stop);
            default:
                return false;
        }
    };

    const isValidStep = (value) => {
        return value === undefined || value.search(/[^\d]/) === -1;
    };

    const validateForRange = (value, start, stop) => {
        if (value.search(/[^\d-,\/*]/) !== -1) {
            return false;
        }

        const list = value.split(',');
        return list.every((condition) => {
            const splits = condition.split('/');
            // Prevents `*/ * * * *` from being accepted.
            if (condition.trim().endsWith('/')) {
                return false;
            }

            // Prevents `*/*/* * * * *` from being accepted
            if (splits.length > 2) {
                return false;
            }

            // If we don't have a `/`, right will be undefined which is considered a valid step if we don't a `/`.
            const [left, right] = splits;
            return isValidRange(left, start, stop) && isValidStep(right);
        })
    };

    const hasValidSeconds = (seconds) => {
        return validateForRange(seconds, 0, 59);
    };

    const hasValidMinutes = (minutes) => {
        return validateForRange(minutes, 0, 59);
    };

    const hasValidHours = (hours) => {
        return validateForRange(hours, 0, 23);
    };

    const hasValidDays = (days, allowBlankDay) => {
        return (allowBlankDay && isQuestionMark(days)) || validateForRange(days, 1, 31);
    };

    const monthAlias = {
        jan: '1',
        feb: '2',
        mar: '3',
        apr: '4',
        may: '5',
        jun: '6',
        jul: '7',
        aug: '8',
        sep: '9',
        oct: '10',
        nov: '11',
        dec: '12'
    };

    const hasValidMonths = (months, alias) => {
        // Prevents alias to be used as steps
        if (months.search(/\/[a-zA-Z]/) !== -1) {
            return false;
        }

        if (alias) {
            const remappedMonths = months.toLowerCase().replace(/[a-z]{3}/g, (match) => {
                return monthAlias[match] === undefined ? match : monthAlias[match]
            });
            // If any invalid alias was used, it won't pass the other checks as there will be non-numeric values in the months
            return validateForRange(remappedMonths, 1, 12);
        }

        return validateForRange(months, 1, 12)
    };

    const weekdaysAlias = {
        sun: '0',
        mon: '1',
        tue: '2',
        wed: '3',
        thu: '4',
        fri: '5',
        sat: '6'
    };

    const hasValidWeekdays = (weekdays, alias, allowBlankDay) => {
        // If there is a question mark, checks if the allowBlankDay flag is set
        if (allowBlankDay && isQuestionMark(weekdays)) {
            return true;
        } else if (!allowBlankDay && isQuestionMark(weekdays)) {
            return false;
        }

        // Prevents alias to be used as steps
        if (weekdays.search(/\/[a-zA-Z]/) !== -1) {
            return false;
        }

        if (alias) {
            const remappedWeekdays = weekdays.toLowerCase().replace(/[a-z]{3}/g, (match) => {
                return weekdaysAlias[match] === undefined ? match : weekdaysAlias[match]
            });
            // If any invalid alias was used, it won't pass the other checks as there will be non-numeric values in the weekdays
            return validateForRange(remappedWeekdays, 0, 6);
        }

        return validateForRange(weekdays, 0, 6);
    };

    const hasCompatibleDayFormat = (days, weekdays, allowBlankDay) => {
        return !(allowBlankDay && isQuestionMark(days) && isQuestionMark(weekdays));
    };

    const split = (cron) => {
        return cron.trim().split(/\s+/);
    };

    const defaultOptions = {
        alias: false,
        seconds: false,
        allowBlankDay: false
    };

    const isValidCron = (cron, options) => {
        options = Object.assign(defaultOptions, options);

        const splits = split(cron);

        if (splits.length > (options.seconds ? 6 : 5) || splits.length < 5) {
            return false;
        }

        const checks = [];

        if (splits.length === 6) {
            const seconds = splits.shift();
            if (seconds) {
                checks.push(hasValidSeconds(seconds));
            }
        }

        // We could only check the steps gradually and return false on the first invalid block,
        // However, this won't have any performance impact so why bother for now.
        const [minutes, hours, days, months, weekdays] = splits;
        checks.push(hasValidMinutes(minutes));
        checks.push(hasValidHours(hours));
        checks.push(hasValidDays(days, options.allowBlankDay));
        checks.push(hasValidMonths(months, options.alias));
        checks.push(hasValidWeekdays(weekdays, options.alias, options.allowBlankDay));
        checks.push(hasCompatibleDayFormat(days, weekdays, options.allowBlankDay));

        return checks.every(Boolean)
    };

    var global_obj = {};
    global_obj.isValidCron = isValidCron;
    return global_obj
});