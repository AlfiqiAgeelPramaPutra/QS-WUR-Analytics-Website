import pandas as pd
import requests
import mysql.connector
from datetime import datetime

# Function to get the last IDs and year from the database
def get_last_ids_and_year(cursor):
    cursor.execute("SELECT MAX(univ_id) FROM univ_info")
    last_univ_id = cursor.fetchone()[0] or 0

    cursor.execute("SELECT MAX(Id_Academic_Reputation) FROM Academic_Reputation")
    last_academic_reputation_id = cursor.fetchone()[0] or 0
    
    cursor.execute("SELECT MAX(Id_Citations_Per_Faculty) FROM Citations_Per_Faculty")
    last_citations_per_faculty_id = cursor.fetchone()[0] or 0
    
    cursor.execute("SELECT MAX(Id_Employer_Reputation) FROM Employer_Reputation")
    last_employer_reputation_id = cursor.fetchone()[0] or 0
    
    cursor.execute("SELECT MAX(Id_Employment_Outcomes) FROM Employment_Outcomes")
    last_employment_outcomes_id = cursor.fetchone()[0] or 0
    
    cursor.execute("SELECT MAX(Id_Faculty_Student_Ratio) FROM Faculty_Student_Ratio")
    last_faculty_student_ratio_id = cursor.fetchone()[0] or 0
    
    cursor.execute("SELECT MAX(Id_International_Faculty_Ratio) FROM International_Faculty_Ratio")
    last_international_faculty_ratio_id = cursor.fetchone()[0] or 0
    
    cursor.execute("SELECT MAX(Id_International_Research_Network) FROM International_Research_Network")
    last_international_research_network_id = cursor.fetchone()[0] or 0
    
    cursor.execute("SELECT MAX(Id_International_Students_Ratio) FROM International_Students_Ratio")
    last_international_students_ratio_id = cursor.fetchone()[0] or 0
    
    cursor.execute("SELECT MAX(Id_Sustainability) FROM Sustainability")
    last_sustainability_id = cursor.fetchone()[0] or 0

    cursor.execute("SELECT MAX(Year) FROM Overall")
    last_year = cursor.fetchone()[0] or datetime.now().year

    return (last_univ_id, last_academic_reputation_id, last_citations_per_faculty_id,
            last_employer_reputation_id, last_employment_outcomes_id, last_faculty_student_ratio_id,
            last_international_faculty_ratio_id, last_international_research_network_id,
            last_international_students_ratio_id, last_sustainability_id, last_year)

# Connect to the database
db_connection = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="pa_qswur_web"
)
cursor = db_connection.cursor()

# Get the last IDs and year from the database
(last_univ_id, last_academic_reputation_id, last_citations_per_faculty_id,
 last_employer_reputation_id, last_employment_outcomes_id, last_faculty_student_ratio_id,
 last_international_faculty_ratio_id, last_international_research_network_id,
 last_international_students_ratio_id, last_sustainability_id, last_year) = get_last_ids_and_year(cursor)

url_template = "https://www.topuniversities.com/rankings/endpoint?nid=3740566&page={}&items_per_page=15&tab=indicators"

all_data = []
academic_reputation_data = []
citations_per_faculty_data = []
employer_reputation_data = []
employment_outcomes_data = []
faculty_student_ratio_data = []
international_faculty_ratio_data = []
international_research_network_data = []
international_students_ratio_data = []
sustainability_data = []
overall_data = []

# Loop through each page
for page in range(120):
    url = url_template.format(page)
    response = requests.get(url)
    if response.status_code == 200:
        data = response.json()
        # Loop through each data item on the page
        for score_node in data['score_nodes']:
            title = score_node['title']
            region = score_node['region']
            country = score_node['country']
            city = score_node['city']
            overall_score = score_node['overall_score']
            rank_display = score_node['rank_display']
            
            # Update year for latest data
            year = last_year + 1

            # Increment ID
            last_univ_id += 1
            univ_id = last_univ_id
            
            # Combine data for 'univ_info' table
            university_data = {
                'univ_id': univ_id,
                'univ_name': title,
                'Region': region,
                'Country': country,
                'Overall_Score': overall_score,
                'Rank': rank_display,
                'Year': year
            }
            all_data.append(university_data)

            # Extract and categorize indicators
            indicators = score_node.get('scores', {})
            for category, indicators_list in indicators.items():
                for indicator in indicators_list:
                    indicator_name = indicator['indicator_name']
                    rank = indicator['rank']
                    score = indicator['score']
                    
                    # Check indicator category and insert data accordingly
                    if category == 'Research & Discovery':
                        if indicator_name == 'Academic Reputation':
                            academic_reputation_data.append({
                                'Id_Academic_Reputation': last_academic_reputation_id + 1,
                                'univ_name': title,
                                'Region': region,
                                'Country': country,
                                'Academic_Reputation_Rank': rank,
                                'Academic_Reputation_Score': score,
                                'Year': year,
                                'univ_id': univ_id
                            })
                            last_academic_reputation_id += 1
                        elif indicator_name == 'Citations per Faculty':
                            citations_per_faculty_data.append({
                                'Id_Citations_Per_Faculty': last_citations_per_faculty_id + 1,
                                'univ_name': title,
                                'Region': region,
                                'Country': country,
                                'Citations_Per_Faculty_Rank': rank,
                                'Citations_Per_Faculty_Score': score,
                                'Year': year,
                                'univ_id': univ_id
                            })
                            last_citations_per_faculty_id += 1
                    
                    elif category == 'Learning Experience':
                        if indicator_name == 'Faculty Student Ratio':
                            faculty_student_ratio_data.append({
                                'Id_Faculty_Student_Ratio': last_faculty_student_ratio_id + 1,
                                'univ_name': title,
                                'Region': region,
                                'Country': country,
                                'Faculty_Student_Ratio_Rank': rank,
                                'Faculty_Student_Ratio_Score': score,
                                'Year': year,
                                'univ_id': univ_id
                            })
                            last_faculty_student_ratio_id += 1
                    
                    elif category == 'Employability':
                        if indicator_name == 'Employer Reputation':
                            employer_reputation_data.append({
                                'Id_Employer_Reputation': last_employer_reputation_id + 1,
                                'univ_name': title,
                                'Region': region,
                                'Country': country,
                                'Employer_Reputation_Rank': rank,
                                'Employer_Reputation_Score': score,
                                'Year': year,
                                'univ_id': univ_id
                            })
                            last_employer_reputation_id += 1
                        elif indicator_name == 'Employment Outcomes':
                            employment_outcomes_data.append({
                                'Id_Employment_Outcomes': last_employment_outcomes_id + 1,
                                'univ_name': title,
                                'Region': region,
                                'Country': country,
                                'Employment_Outcomes_Rank': rank,
                                'Employment_Outcomes_Score': score,
                                'Year': year,
                                'univ_id': univ_id
                            })
                            last_employment_outcomes_id += 1
                    
                    elif category == 'Global Engagement':
                        if indicator_name == 'International Student Ratio':
                            international_students_ratio_data.append({
                                'Id_International_Students_Ratio': last_international_students_ratio_id + 1,
                                'univ_name': title,
                                'Region': region,
                                'Country': country,
                                'International_Students_Ratio_Rank': rank,
                                'International_Students_Ratio_Score': score,
                                'Year': year,
                                'univ_id': univ_id
                            })
                            last_international_students_ratio_id += 1
                        elif indicator_name == 'International Research Network':
                            international_research_network_data.append({
                                'Id_International_Research_Network': last_international_research_network_id + 1,
                                'univ_name': title,
                                'Region': region,
                                'Country': country,
                                'International_Research_Network_Rank': rank,
                                'International_Research_Network_Score': score,
                                'Year': year,
                                'univ_id': univ_id
                            })
                            last_international_research_network_id += 1
                        elif indicator_name == 'International Faculty Ratio':
                            international_faculty_ratio_data.append({
                                'Id_International_Faculty_Ratio': last_international_faculty_ratio_id + 1,
                                'univ_name': title,
                                'Region': region,
                                'Country': country,
                                'International_Faculty_Ratio_Rank': rank,
                                'International_Faculty_Ratio_Score': score,
                                'Year': year,
                                'univ_id': univ_id
                            })
                            last_international_faculty_ratio_id += 1
                    
                    elif category == 'Sustainability':
                        if indicator_name == 'Sustainability':
                            sustainability_data.append({
                                'Id_Sustainability': last_sustainability_id + 1,
                                'univ_name': title,
                                'Region': region,
                                'Country': country,
                                'Sustainability_Rank': rank,
                                'Sustainability_Score': score,
                                'Year': year,
                                'univ_id': univ_id
                            })
                            last_sustainability_id += 1
                        
            # Overall data for completeness
            overall_data.append({
            'Id_Overall': last_univ_id,
            'univ_name': title,
            'Region': region,
            'Country': country,
            'Overall_Score': overall_score,
            'Rank': rank_display,
            'Academic_Reputation_Rank': next((item['Academic_Reputation_Rank'] for item in academic_reputation_data if item['univ_name'] == title), None),
            'Academic_Reputation_Score': next((item['Academic_Reputation_Score'] for item in academic_reputation_data if item['univ_name'] == title), None),
            'Citations_Per_Faculty_Rank': next((item['Citations_Per_Faculty_Rank'] for item in citations_per_faculty_data if item['univ_name'] == title), None),
            'Citations_Per_Faculty_Score': next((item['Citations_Per_Faculty_Score'] for item in citations_per_faculty_data if item['univ_name'] == title), None),
            'Faculty_Student_Ratio_Rank': next((item['Faculty_Student_Ratio_Rank'] for item in faculty_student_ratio_data if item['univ_name'] == title), None),
            'Faculty_Student_Ratio_Score': next((item['Faculty_Student_Ratio_Score'] for item in faculty_student_ratio_data if item['univ_name'] == title), None),
            'Employer_Reputation_Rank': next((item['Employer_Reputation_Rank'] for item in employer_reputation_data if item['univ_name'] == title), None),
            'Employer_Reputation_Score': next((item['Employer_Reputation_Score'] for item in employer_reputation_data if item['univ_name'] == title), None),
            'Employment_Outcomes_Rank': next((item['Employment_Outcomes_Rank'] for item in employment_outcomes_data if item['univ_name'] == title), None),
            'Employment_Outcomes_Score': next((item['Employment_Outcomes_Score'] for item in employment_outcomes_data if item['univ_name'] == title), None),
            'International_Students_Ratio_Rank': next((item['International_Students_Ratio_Rank'] for item in international_students_ratio_data if item['univ_name'] == title), None),
            'International_Students_Ratio_Score': next((item['International_Students_Ratio_Score'] for item in international_students_ratio_data if item['univ_name'] == title), None),
            'International_Research_Network_Rank': next((item['International_Research_Network_Rank'] for item in international_research_network_data if item['univ_name'] == title), None),
            'International_Research_Network_Score': next((item['International_Research_Network_Score'] for item in international_research_network_data if item['univ_name'] == title), None),
            'International_Faculty_Ratio_Rank': next((item['International_Faculty_Ratio_Rank'] for item in international_faculty_ratio_data if item['univ_name'] == title), None),
            'International_Faculty_Ratio_Score': next((item['International_Faculty_Ratio_Score'] for item in international_faculty_ratio_data if item['univ_name'] == title), None),
            'Sustainability_Rank': next((item['Sustainability_Rank'] for item in sustainability_data if item['univ_name'] == title), None),
            'Sustainability_Score': next((item['Sustainability_Score'] for item in sustainability_data if item['univ_name'] == title), None),
            'Year': year,
            'univ_id': univ_id,
            'Id_Academic_Reputation': last_academic_reputation_id,
            'Id_Citations_Per_Faculty': last_citations_per_faculty_id,
            'Id_Employer_Reputation': last_employer_reputation_id,
            'Id_Employment_Outcomes': last_employment_outcomes_id,
            'Id_Faculty_Student_Ratio': last_faculty_student_ratio_id,
            'Id_International_Faculty_Ratio': last_international_faculty_ratio_id,
            'Id_International_Research_Network': last_international_research_network_id,
            'Id_International_Students_Ratio': last_international_students_ratio_id,
            'Id_Sustainability': last_sustainability_id
        })

# Convert lists to DataFrames
df_all = pd.DataFrame(all_data)
df_academic_reputation = pd.DataFrame(academic_reputation_data)
df_citations_per_faculty = pd.DataFrame(citations_per_faculty_data)
df_employer_reputation = pd.DataFrame(employer_reputation_data)
df_employment_outcomes = pd.DataFrame(employment_outcomes_data)
df_faculty_student_ratio = pd.DataFrame(faculty_student_ratio_data)
df_international_faculty_ratio = pd.DataFrame(international_faculty_ratio_data)
df_international_research_network = pd.DataFrame(international_research_network_data)
df_international_students_ratio = pd.DataFrame(international_students_ratio_data)
df_sustainability = pd.DataFrame(sustainability_data)
df_overall = pd.DataFrame(overall_data)

# Mengganti nilai 'n/a', string kosong, dan NaN menjadi 0
df_all.replace(['n/a', ''], 0, inplace=True)
df_all.fillna(0, inplace=True)

df_academic_reputation.replace(['n/a', ''], 0, inplace=True)
df_academic_reputation.fillna(0, inplace=True)

df_citations_per_faculty.replace(['n/a', ''], 0, inplace=True)
df_citations_per_faculty.fillna(0, inplace=True)

df_employer_reputation.replace(['n/a', ''], 0, inplace=True)
df_employer_reputation.fillna(0, inplace=True)

df_employment_outcomes.replace(['n/a', ''], 0, inplace=True)
df_employment_outcomes.fillna(0, inplace=True)

df_faculty_student_ratio.replace(['n/a', ''], 0, inplace=True)
df_faculty_student_ratio.fillna(0, inplace=True)

df_international_faculty_ratio.replace(['n/a', ''], 0, inplace=True)
df_international_faculty_ratio.fillna(0, inplace=True)

df_international_research_network.replace(['n/a', ''], 0, inplace=True)
df_international_research_network.fillna(0, inplace=True)

df_international_students_ratio.replace(['n/a', ''], 0, inplace=True)
df_international_students_ratio.fillna(0, inplace=True)

df_sustainability.replace(['n/a', ''], 0, inplace=True)
df_sustainability.fillna(0, inplace=True)

df_overall.replace(['n/a', ''], 0, inplace=True)
df_overall.fillna(0, inplace=True)

# Save DataFrames to CSV
df_all.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\univ_info.csv", index=False)
df_academic_reputation.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\academic_reputation.csv", index=False)
df_citations_per_faculty.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\citations_per_faculty.csv", index=False)
df_employer_reputation.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\employer_reputation.csv", index=False)
df_employment_outcomes.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\employment_outcomes.csv", index=False)
df_faculty_student_ratio.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\faculty_student_ratio.csv", index=False)
df_international_faculty_ratio.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\international_faculty_ratio.csv", index=False)
df_international_research_network.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\international_research_network.csv", index=False)
df_international_students_ratio.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\international_students_ratio.csv", index=False)
df_sustainability.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\sustainability.csv", index=False)
df_overall.to_csv(r"C:\Users\BRAVO 15\Documents\Alfiqi Ageel\Semester 5\Metode Penelitian\scraping\2022\overall.csv", index=False)

# Close the database connection
cursor.close()
db_connection.close()

print("Data telah disimpan dalam format CSV dengan nilai 'n/a' diganti menjadi 0.")
