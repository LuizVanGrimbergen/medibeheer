export function normalizeDoctorPatientSearchQuery(value: string): string {
    return value.trim().toLocaleLowerCase('nl-NL');
}

export function doctorPatientMatchesSearchQuery(
    patientName: string,
    query: string,
): boolean {
    if (query === '') {
        return true;
    }

    return normalizeDoctorPatientSearchQuery(patientName).includes(query);
}
