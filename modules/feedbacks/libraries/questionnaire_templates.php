<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Built-in editable construction-business questionnaire templates. */
return json_decode(<<<'JSON'
[
  {
    "title": "Customer Experience & Satisfaction",
    "audience": "Customers",
    "description": "Measure communication, workmanship, cleanliness, schedule and overall satisfaction.",
    "questions": [
      {
        "type": "radio",
        "question": "How satisfied are you with your overall experience?",
        "options": [
          "Very satisfied",
          "Satisfied",
          "Neutral",
          "Dissatisfied",
          "Very dissatisfied"
        ]
      },
      {
        "type": "radio",
        "question": "How clearly did we explain the scope, price and schedule?",
        "options": [
          "Very clearly",
          "Clearly",
          "Somewhat clearly",
          "Not clearly"
        ]
      },
      {
        "type": "radio",
        "question": "How would you rate communication during the project?",
        "options": [
          "Excellent",
          "Good",
          "Fair",
          "Poor"
        ]
      },
      {
        "type": "radio",
        "question": "Was the work completed in accordance with the agreed scope?",
        "options": [
          "Yes, completely",
          "Mostly",
          "Partially",
          "No",
          "Not applicable"
        ]
      },
      {
        "type": "radio",
        "question": "How would you rate workmanship and attention to detail?",
        "options": [
          "Excellent",
          "Good",
          "Fair",
          "Poor"
        ]
      },
      {
        "type": "radio",
        "question": "Was the jobsite kept reasonably clean and safe?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Rarely",
          "Not applicable"
        ]
      },
      {
        "type": "radio",
        "question": "Was the project completed on the expected schedule?",
        "options": [
          "Early",
          "On time",
          "Slightly delayed",
          "Significantly delayed"
        ]
      },
      {
        "type": "radio",
        "question": "Would you recommend Smart Choice Contractors USA?",
        "options": [
          "Definitely",
          "Probably",
          "Not sure",
          "Probably not",
          "Definitely not"
        ]
      },
      {
        "type": "textarea",
        "question": "What did our team do particularly well?",
        "options": []
      },
      {
        "type": "textarea",
        "question": "What should we improve on your next project?",
        "options": []
      },
      {
        "type": "input",
        "question": "May we contact you about your feedback? (Yes/No)",
        "options": []
      }
    ]
  },
  {
    "title": "New Customer / Prospect Intake",
    "audience": "Prospects / Leads",
    "description": "Qualify a construction inquiry before estimating.",
    "questions": [
      {
        "type": "input",
        "question": "Full name",
        "options": []
      },
      {
        "type": "input",
        "question": "Email and best phone number",
        "options": []
      },
      {
        "type": "input",
        "question": "Project property address",
        "options": []
      },
      {
        "type": "radio",
        "question": "What type of project are you considering?",
        "options": [
          "Kitchen remodel",
          "Bathroom remodel",
          "Whole-home renovation",
          "Roofing",
          "Deck / outdoor living",
          "Painting / drywall",
          "Electrical",
          "Other"
        ]
      },
      {
        "type": "textarea",
        "question": "Describe the work you would like completed.",
        "options": []
      },
      {
        "type": "radio",
        "question": "When would you like the project to begin?",
        "options": [
          "Immediately",
          "Within 30 days",
          "1–3 months",
          "3–6 months",
          "Just researching"
        ]
      },
      {
        "type": "radio",
        "question": "What is your approximate project budget?",
        "options": [
          "Under $5,000",
          "$5,000–$15,000",
          "$15,000–$30,000",
          "$30,000–$60,000",
          "$60,000–$100,000",
          "Over $100,000",
          "Not determined"
        ]
      },
      {
        "type": "radio",
        "question": "Do you own the property?",
        "options": [
          "Yes",
          "No",
          "Under contract"
        ]
      },
      {
        "type": "checkbox",
        "question": "What matters most when selecting a contractor?",
        "options": [
          "Quality",
          "Price",
          "Schedule",
          "Communication",
          "Warranty",
          "Licensed and insured",
          "Design assistance"
        ]
      },
      {
        "type": "textarea",
        "question": "What questions or concerns should our estimator address?",
        "options": []
      }
    ]
  },
  {
    "title": "Pre-Construction Project Discovery",
    "audience": "Customers / Prospects",
    "description": "Capture project scope, site conditions, priorities and decision criteria.",
    "questions": [
      {
        "type": "input",
        "question": "Project name and property address",
        "options": []
      },
      {
        "type": "textarea",
        "question": "Describe the desired finished result.",
        "options": []
      },
      {
        "type": "checkbox",
        "question": "Which spaces or systems are included?",
        "options": [
          "Kitchen",
          "Bathrooms",
          "Bedrooms",
          "Living areas",
          "Roof",
          "Exterior",
          "Deck / patio",
          "Electrical",
          "Plumbing",
          "Other"
        ]
      },
      {
        "type": "radio",
        "question": "Is the property occupied during construction?",
        "options": [
          "Yes",
          "No",
          "Partially"
        ]
      },
      {
        "type": "radio",
        "question": "Who will approve selections and change orders?",
        "options": [
          "One owner",
          "Multiple owners",
          "Property manager",
          "Other"
        ]
      },
      {
        "type": "radio",
        "question": "Are drawings, plans or specifications available?",
        "options": [
          "Yes, complete",
          "Some documents",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "What is the target completion date?",
        "options": [
          "Flexible",
          "Within 30 days",
          "1–3 months",
          "3–6 months",
          "Fixed date"
        ]
      },
      {
        "type": "textarea",
        "question": "List known site conditions, access restrictions or special requirements.",
        "options": []
      },
      {
        "type": "textarea",
        "question": "List your top three project priorities.",
        "options": []
      },
      {
        "type": "textarea",
        "question": "What would make this project a success for you?",
        "options": []
      }
    ]
  },
  {
    "title": "Post-Estimate Follow-Up",
    "audience": "Prospects",
    "description": "Understand estimate clarity, objections and sales follow-up needs.",
    "questions": [
      {
        "type": "radio",
        "question": "Did you receive and review the estimate?",
        "options": [
          "Yes",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "Was the scope of work easy to understand?",
        "options": [
          "Very clear",
          "Mostly clear",
          "Somewhat unclear",
          "Very unclear"
        ]
      },
      {
        "type": "radio",
        "question": "Was the pricing presented clearly?",
        "options": [
          "Yes",
          "Mostly",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "How does our proposal compare with your expectations?",
        "options": [
          "Better than expected",
          "As expected",
          "Higher than expected",
          "Lower than expected"
        ]
      },
      {
        "type": "radio",
        "question": "What is your current decision status?",
        "options": [
          "Ready to proceed",
          "Comparing proposals",
          "Need revisions",
          "Postponed",
          "Selected another contractor"
        ]
      },
      {
        "type": "checkbox",
        "question": "What factors are influencing your decision?",
        "options": [
          "Price",
          "Scope of work",
          "Timeline",
          "Financing",
          "Trust / references",
          "Warranty",
          "Other"
        ]
      },
      {
        "type": "textarea",
        "question": "What changes or clarification would help you move forward?",
        "options": []
      },
      {
        "type": "radio",
        "question": "Would you like us to contact you?",
        "options": [
          "Yes",
          "No",
          "At a later date"
        ]
      },
      {
        "type": "input",
        "question": "Preferred follow-up date or time",
        "options": []
      }
    ]
  },
  {
    "title": "Project Progress & Communication Pulse",
    "audience": "Active customers",
    "description": "Catch concerns early during active construction.",
    "questions": [
      {
        "type": "radio",
        "question": "Do you know the current project status?",
        "options": [
          "Yes, clearly",
          "Generally",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "Are schedule updates being communicated promptly?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Rarely"
        ]
      },
      {
        "type": "radio",
        "question": "Are questions and messages answered in a timely manner?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Rarely"
        ]
      },
      {
        "type": "radio",
        "question": "Is the work area being protected and maintained?",
        "options": [
          "Very well",
          "Adequately",
          "Needs improvement",
          "Not applicable"
        ]
      },
      {
        "type": "radio",
        "question": "Are completed work items meeting expectations?",
        "options": [
          "Yes",
          "Mostly",
          "Some concerns",
          "Major concerns"
        ]
      },
      {
        "type": "checkbox",
        "question": "Do you need attention on any of these items?",
        "options": [
          "Schedule",
          "Workmanship",
          "Cleanliness",
          "Material selection",
          "Change order",
          "Communication",
          "Access / safety",
          "Other"
        ]
      },
      {
        "type": "textarea",
        "question": "Describe any concern that needs follow-up.",
        "options": []
      },
      {
        "type": "radio",
        "question": "How urgent is the concern?",
        "options": [
          "Routine",
          "Needs attention this week",
          "Urgent / stop-work concern"
        ]
      },
      {
        "type": "textarea",
        "question": "What would improve your experience during the remainder of the project?",
        "options": []
      }
    ]
  },
  {
    "title": "Project Completion & Handover",
    "audience": "Customers",
    "description": "Document acceptance, outstanding items and handover quality.",
    "questions": [
      {
        "type": "radio",
        "question": "Has the agreed scope been completed?",
        "options": [
          "Yes",
          "Mostly",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "Have you inspected the completed work?",
        "options": [
          "Yes",
          "Not yet"
        ]
      },
      {
        "type": "radio",
        "question": "Are there outstanding punch-list items?",
        "options": [
          "No",
          "Yes, minor",
          "Yes, significant"
        ]
      },
      {
        "type": "textarea",
        "question": "List each outstanding item and its location.",
        "options": []
      },
      {
        "type": "radio",
        "question": "Were operating instructions and product information provided where applicable?",
        "options": [
          "Yes",
          "Some",
          "No",
          "Not applicable"
        ]
      },
      {
        "type": "radio",
        "question": "Do you understand how to request warranty service?",
        "options": [
          "Yes",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "How satisfied are you with the final workmanship?",
        "options": [
          "Very satisfied",
          "Satisfied",
          "Neutral",
          "Dissatisfied"
        ]
      },
      {
        "type": "radio",
        "question": "Would you consider Smart Choice for future work?",
        "options": [
          "Definitely",
          "Probably",
          "Not sure",
          "No"
        ]
      },
      {
        "type": "textarea",
        "question": "Additional handover comments or photos to provide (describe files).",
        "options": []
      }
    ]
  },
  {
    "title": "Warranty & Service Request",
    "audience": "Customers",
    "description": "Collect structured warranty and post-completion service information.",
    "questions": [
      {
        "type": "input",
        "question": "Customer name and project address",
        "options": []
      },
      {
        "type": "input",
        "question": "Original project or invoice reference",
        "options": []
      },
      {
        "type": "radio",
        "question": "What type of issue are you reporting?",
        "options": [
          "Workmanship",
          "Material / product",
          "Water intrusion",
          "Electrical",
          "Finish / cosmetic",
          "Other"
        ]
      },
      {
        "type": "textarea",
        "question": "Describe the issue and when it was first noticed.",
        "options": []
      },
      {
        "type": "input",
        "question": "Room, area or exact location of issue",
        "options": []
      },
      {
        "type": "radio",
        "question": "Is the issue actively causing damage or creating a safety concern?",
        "options": [
          "Yes",
          "No",
          "Unsure"
        ]
      },
      {
        "type": "radio",
        "question": "When can our team access the property?",
        "options": [
          "Weekday morning",
          "Weekday afternoon",
          "Saturday",
          "Flexible"
        ]
      },
      {
        "type": "textarea",
        "question": "What troubleshooting or temporary steps have already been taken?",
        "options": []
      },
      {
        "type": "input",
        "question": "Best contact information",
        "options": []
      }
    ]
  },
  {
    "title": "Subcontractor Qualification & Onboarding",
    "audience": "Subcontractors",
    "description": "Prequalify trades, licensing, insurance, capacity and references.",
    "questions": [
      {
        "type": "input",
        "question": "Legal business name and primary contact",
        "options": []
      },
      {
        "type": "input",
        "question": "Business address, phone and email",
        "options": []
      },
      {
        "type": "checkbox",
        "question": "Which trades do you perform?",
        "options": [
          "Roofing",
          "Electrical",
          "Plumbing",
          "HVAC",
          "Framing",
          "Drywall",
          "Painting",
          "Tile",
          "Flooring",
          "Cabinetry",
          "Decks",
          "Concrete",
          "Other"
        ]
      },
      {
        "type": "input",
        "question": "License type, number and expiration (if applicable)",
        "options": []
      },
      {
        "type": "input",
        "question": "General liability carrier and policy expiration",
        "options": []
      },
      {
        "type": "input",
        "question": "Workers compensation coverage or exemption details",
        "options": []
      },
      {
        "type": "radio",
        "question": "Can you provide W-9, insurance certificates and required licenses?",
        "options": [
          "Yes",
          "No",
          "In progress"
        ]
      },
      {
        "type": "radio",
        "question": "Typical crew size",
        "options": [
          "1",
          "2–3",
          "4–6",
          "7–10",
          "More than 10"
        ]
      },
      {
        "type": "radio",
        "question": "Current availability",
        "options": [
          "Available now",
          "Within 2 weeks",
          "Within 30 days",
          "Booked"
        ]
      },
      {
        "type": "textarea",
        "question": "List three recent comparable projects and references.",
        "options": []
      },
      {
        "type": "textarea",
        "question": "Describe your safety program and quality-control process.",
        "options": []
      }
    ]
  },
  {
    "title": "Subcontractor Project Performance Review",
    "audience": "Subcontractors",
    "description": "Evaluate trade partner quality, schedule, communication and safety.",
    "questions": [
      {
        "type": "radio",
        "question": "Was the work completed to plans, specifications and approved scope?",
        "options": [
          "Excellent",
          "Good",
          "Needs improvement",
          "Unacceptable"
        ]
      },
      {
        "type": "radio",
        "question": "Was the subcontractor on schedule?",
        "options": [
          "Ahead",
          "On time",
          "Minor delay",
          "Major delay"
        ]
      },
      {
        "type": "radio",
        "question": "Was the crew reliable and adequately staffed?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Rarely"
        ]
      },
      {
        "type": "radio",
        "question": "Was the jobsite left clean and orderly?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Rarely"
        ]
      },
      {
        "type": "radio",
        "question": "Were safety requirements followed?",
        "options": [
          "Consistently",
          "Mostly",
          "Inconsistently",
          "Serious concern"
        ]
      },
      {
        "type": "radio",
        "question": "Were change requests communicated before extra work?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Never"
        ]
      },
      {
        "type": "radio",
        "question": "Was the invoice accurate and supported?",
        "options": [
          "Yes",
          "Minor corrections",
          "Major corrections"
        ]
      },
      {
        "type": "textarea",
        "question": "Describe strengths and issues with specific examples.",
        "options": []
      },
      {
        "type": "radio",
        "question": "Would you hire this subcontractor again?",
        "options": [
          "Definitely",
          "Yes, with conditions",
          "Unsure",
          "No"
        ]
      },
      {
        "type": "textarea",
        "question": "Corrective actions or training required before next assignment.",
        "options": []
      }
    ]
  },
  {
    "title": "Daily Site Safety Observation",
    "audience": "Field teams / subcontractors",
    "description": "Capture site hazards and corrective actions; not a substitute for required safety programs.",
    "questions": [
      {
        "type": "input",
        "question": "Project name, address and inspection date",
        "options": []
      },
      {
        "type": "input",
        "question": "Inspector name",
        "options": []
      },
      {
        "type": "checkbox",
        "question": "Which conditions were checked?",
        "options": [
          "PPE",
          "Fall protection",
          "Ladders / scaffolds",
          "Electrical safety",
          "Housekeeping",
          "Access / egress",
          "Fire prevention",
          "Tool condition",
          "Public protection",
          "Hazard communication"
        ]
      },
      {
        "type": "radio",
        "question": "Were immediate hazards identified?",
        "options": [
          "No",
          "Yes, corrected immediately",
          "Yes, correction pending"
        ]
      },
      {
        "type": "textarea",
        "question": "Describe each hazard, location and people potentially affected.",
        "options": []
      },
      {
        "type": "radio",
        "question": "Was work stopped where an imminent danger existed?",
        "options": [
          "Yes",
          "No",
          "Not applicable"
        ]
      },
      {
        "type": "textarea",
        "question": "Corrective action, responsible person and due date.",
        "options": []
      },
      {
        "type": "radio",
        "question": "Does the area need re-inspection?",
        "options": [
          "Yes",
          "No"
        ]
      },
      {
        "type": "input",
        "question": "Follow-up inspection date",
        "options": []
      }
    ]
  },
  {
    "title": "Jobsite Quality Control Inspection",
    "audience": "Field teams / project managers",
    "description": "Verify workmanship against approved scope and record deficiencies.",
    "questions": [
      {
        "type": "input",
        "question": "Project, inspection date and inspector",
        "options": []
      },
      {
        "type": "checkbox",
        "question": "Which work areas were inspected?",
        "options": [
          "Framing",
          "Roofing",
          "Electrical",
          "Plumbing",
          "Drywall",
          "Tile",
          "Cabinetry",
          "Flooring",
          "Paint",
          "Exterior / deck",
          "Final cleanup"
        ]
      },
      {
        "type": "radio",
        "question": "Does work match approved plans and specifications?",
        "options": [
          "Pass",
          "Conditional pass",
          "Fail",
          "Not inspected"
        ]
      },
      {
        "type": "radio",
        "question": "Are materials and products correct and undamaged?",
        "options": [
          "Pass",
          "Conditional pass",
          "Fail"
        ]
      },
      {
        "type": "radio",
        "question": "Are finish details acceptable?",
        "options": [
          "Pass",
          "Conditional pass",
          "Fail"
        ]
      },
      {
        "type": "textarea",
        "question": "List deficiencies with location, photo reference and severity.",
        "options": []
      },
      {
        "type": "textarea",
        "question": "Required corrective action and responsible trade.",
        "options": []
      },
      {
        "type": "input",
        "question": "Reinspection due date",
        "options": []
      },
      {
        "type": "radio",
        "question": "Can the next trade proceed?",
        "options": [
          "Yes",
          "No",
          "With restrictions"
        ]
      }
    ]
  },
  {
    "title": "Change Order & Scope Approval",
    "audience": "Customers",
    "description": "Capture customer understanding and documented approval of scope and cost changes.",
    "questions": [
      {
        "type": "input",
        "question": "Project address and change order number",
        "options": []
      },
      {
        "type": "textarea",
        "question": "Describe requested change and reason.",
        "options": []
      },
      {
        "type": "textarea",
        "question": "Describe additions, deletions, materials and exclusions.",
        "options": []
      },
      {
        "type": "radio",
        "question": "Was the cost impact explained?",
        "options": [
          "Yes",
          "No"
        ]
      },
      {
        "type": "input",
        "question": "Approved price adjustment (numeric)",
        "options": []
      },
      {
        "type": "radio",
        "question": "Was the schedule impact explained?",
        "options": [
          "Yes",
          "No",
          "No schedule impact"
        ]
      },
      {
        "type": "input",
        "question": "Estimated number of calendar days added or removed",
        "options": []
      },
      {
        "type": "radio",
        "question": "Do you understand this change may affect the completion date?",
        "options": [
          "Yes",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "Do you approve proceeding with this change?",
        "options": [
          "Approve",
          "Decline",
          "Need clarification"
        ]
      },
      {
        "type": "input",
        "question": "Customer name and approval date",
        "options": []
      }
    ]
  },
  {
    "title": "Material & Supplier Evaluation",
    "audience": "Project managers / purchasing",
    "description": "Rate suppliers on availability, accuracy, delivery and issue resolution.",
    "questions": [
      {
        "type": "input",
        "question": "Supplier name and order / PO reference",
        "options": []
      },
      {
        "type": "radio",
        "question": "Were quoted prices accurate and competitive?",
        "options": [
          "Excellent",
          "Good",
          "Fair",
          "Poor"
        ]
      },
      {
        "type": "radio",
        "question": "Were materials delivered on the promised date?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Never"
        ]
      },
      {
        "type": "radio",
        "question": "Was the order complete and correct?",
        "options": [
          "Yes",
          "Minor issue",
          "Major issue"
        ]
      },
      {
        "type": "radio",
        "question": "Was material quality acceptable?",
        "options": [
          "Excellent",
          "Good",
          "Fair",
          "Poor"
        ]
      },
      {
        "type": "radio",
        "question": "How quickly were shortages or damages resolved?",
        "options": [
          "Very quickly",
          "Acceptably",
          "Slowly",
          "Not resolved"
        ]
      },
      {
        "type": "checkbox",
        "question": "Issues encountered",
        "options": [
          "Late delivery",
          "Short shipment",
          "Damaged goods",
          "Wrong item",
          "Price variance",
          "Communication",
          "No issues"
        ]
      },
      {
        "type": "textarea",
        "question": "Provide details and recommended improvements.",
        "options": []
      },
      {
        "type": "radio",
        "question": "Would you use this supplier again?",
        "options": [
          "Yes",
          "With conditions",
          "No"
        ]
      }
    ]
  },
  {
    "title": "Employee Satisfaction & Engagement",
    "audience": "Employees",
    "description": "Measure workplace experience, support, fairness, engagement and retention.",
    "questions": [
      {
        "type": "radio",
        "question": "How satisfied are you working here?",
        "options": [
          "Very satisfied",
          "Satisfied",
          "Neutral",
          "Dissatisfied",
          "Very dissatisfied"
        ]
      },
      {
        "type": "radio",
        "question": "Do you feel respected and treated fairly?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Rarely",
          "Never"
        ]
      },
      {
        "type": "radio",
        "question": "Do you understand your responsibilities and priorities?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Rarely"
        ]
      },
      {
        "type": "radio",
        "question": "Do you have the tools and information to do your job well?",
        "options": [
          "Yes, all",
          "Most",
          "Some gaps",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "Does management listen to employee concerns?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Rarely",
          "Never"
        ]
      },
      {
        "type": "radio",
        "question": "Is your workload manageable?",
        "options": [
          "Yes",
          "Mostly",
          "Sometimes",
          "No"
        ]
      },
      {
        "type": "checkbox",
        "question": "What should the company improve?",
        "options": [
          "Training",
          "Communication",
          "Scheduling",
          "Tools / equipment",
          "Workload",
          "Recognition",
          "Safety",
          "Processes",
          "Technology",
          "Other"
        ]
      },
      {
        "type": "textarea",
        "question": "What is one change that would improve your work experience?",
        "options": []
      },
      {
        "type": "textarea",
        "question": "What does the company do well that should continue?",
        "options": []
      },
      {
        "type": "radio",
        "question": "Would you recommend this company as a workplace?",
        "options": [
          "Definitely",
          "Probably",
          "Unsure",
          "Probably not",
          "No"
        ]
      }
    ]
  },
  {
    "title": "Employee Training & Skills Assessment",
    "audience": "Employees / supervisors",
    "description": "Identify competencies, training gaps and development interests.",
    "questions": [
      {
        "type": "input",
        "question": "Employee name, role and department",
        "options": []
      },
      {
        "type": "checkbox",
        "question": "Which skills are part of your current role?",
        "options": [
          "Customer communication",
          "CRM data entry",
          "Estimating",
          "Scheduling",
          "Project coordination",
          "Safety",
          "Trade skills",
          "Quality control",
          "Purchasing",
          "Documentation"
        ]
      },
      {
        "type": "radio",
        "question": "How confident are you performing your current duties?",
        "options": [
          "Very confident",
          "Confident",
          "Somewhat confident",
          "Not confident"
        ]
      },
      {
        "type": "checkbox",
        "question": "Which training would help you most?",
        "options": [
          "CRM / software",
          "Customer service",
          "Construction methods",
          "Safety",
          "Estimating",
          "Leadership",
          "Time management",
          "Written communication",
          "Problem solving"
        ]
      },
      {
        "type": "textarea",
        "question": "Which tasks do you need more guidance on?",
        "options": []
      },
      {
        "type": "radio",
        "question": "Do you have a clear development goal?",
        "options": [
          "Yes",
          "No",
          "Need help defining one"
        ]
      },
      {
        "type": "textarea",
        "question": "What skill would you like to develop in the next six months?",
        "options": []
      },
      {
        "type": "textarea",
        "question": "What support or resources would help you succeed?",
        "options": []
      }
    ]
  },
  {
    "title": "Employee Exit Interview",
    "audience": "Departing employees",
    "description": "Understand retention factors and identify organizational improvements.",
    "questions": [
      {
        "type": "radio",
        "question": "What is the primary reason for leaving?",
        "options": [
          "Career opportunity",
          "Compensation",
          "Schedule / workload",
          "Management",
          "Work environment",
          "Relocation",
          "Personal reasons",
          "Other"
        ]
      },
      {
        "type": "radio",
        "question": "Did the role match what was described when hired?",
        "options": [
          "Yes",
          "Mostly",
          "Partially",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "Did you receive adequate training?",
        "options": [
          "Yes",
          "Some",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "Were expectations and feedback clear?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Never"
        ]
      },
      {
        "type": "radio",
        "question": "Did you feel respected and supported?",
        "options": [
          "Always",
          "Usually",
          "Sometimes",
          "Never"
        ]
      },
      {
        "type": "textarea",
        "question": "What could have influenced you to stay?",
        "options": []
      },
      {
        "type": "textarea",
        "question": "What should management change?",
        "options": []
      },
      {
        "type": "radio",
        "question": "Would you consider returning in the future?",
        "options": [
          "Yes",
          "Maybe",
          "No"
        ]
      },
      {
        "type": "textarea",
        "question": "Any additional feedback you want leadership to know?",
        "options": []
      }
    ]
  },
  {
    "title": "Referral & Reputation Feedback",
    "audience": "Past customers / prospects",
    "description": "Measure referral willingness and identify reputation drivers.",
    "questions": [
      {
        "type": "radio",
        "question": "How likely are you to recommend Smart Choice to someone you know?",
        "options": [
          "10 - Extremely likely",
          "9",
          "8",
          "7",
          "6",
          "5",
          "4",
          "3",
          "2",
          "1",
          "0 - Not at all likely"
        ]
      },
      {
        "type": "radio",
        "question": "What most influenced your rating?",
        "options": [
          "Work quality",
          "Communication",
          "Price / value",
          "Schedule",
          "Professionalism",
          "Problem resolution",
          "Other"
        ]
      },
      {
        "type": "radio",
        "question": "Where did you first hear about us?",
        "options": [
          "Google",
          "Facebook",
          "Nextdoor",
          "Referral",
          "Website",
          "Yard sign",
          "Other"
        ]
      },
      {
        "type": "textarea",
        "question": "What should we tell future customers about working with us?",
        "options": []
      },
      {
        "type": "radio",
        "question": "May we request a public review?",
        "options": [
          "Yes",
          "No"
        ]
      },
      {
        "type": "radio",
        "question": "May we contact you about a referral?",
        "options": [
          "Yes",
          "No"
        ]
      },
      {
        "type": "textarea",
        "question": "What would make you more comfortable recommending us?",
        "options": []
      }
    ]
  },
  {
    "title": "HOA / Property Manager Coordination",
    "audience": "HOA / property managers",
    "description": "Coordinate approvals, access, work hours, documentation and closeout.",
    "questions": [
      {
        "type": "input",
        "question": "Association / property manager and property address",
        "options": []
      },
      {
        "type": "input",
        "question": "Primary contact name, phone and email",
        "options": []
      },
      {
        "type": "radio",
        "question": "Is formal architectural or HOA approval required?",
        "options": [
          "Yes",
          "No",
          "Unsure"
        ]
      },
      {
        "type": "textarea",
        "question": "List required application documents and approval timeline.",
        "options": []
      },
      {
        "type": "input",
        "question": "Permitted work hours and restricted days",
        "options": []
      },
      {
        "type": "checkbox",
        "question": "Required coordination items",
        "options": [
          "Certificate of insurance",
          "License documentation",
          "Work schedule",
          "Resident notice",
          "Parking plan",
          "Delivery plan",
          "Protection plan",
          "Closeout documents"
        ]
      },
      {
        "type": "textarea",
        "question": "Access, staging, noise or resident-notification requirements.",
        "options": []
      },
      {
        "type": "radio",
        "question": "Who is authorized to approve changes?",
        "options": [
          "Property manager",
          "Board / committee",
          "Owner",
          "Other"
        ]
      },
      {
        "type": "textarea",
        "question": "Additional coordination concerns.",
        "options": []
      }
    ]
  },
  {
    "title": "Commercial Client Requirements & Facilities",
    "audience": "Commercial customers",
    "description": "Capture operational constraints and facility requirements for commercial work.",
    "questions": [
      {
        "type": "input",
        "question": "Business name, facility address and decision maker",
        "options": []
      },
      {
        "type": "textarea",
        "question": "Describe the business operation and requested work.",
        "options": []
      },
      {
        "type": "radio",
        "question": "Will the business remain operational during construction?",
        "options": [
          "Yes",
          "No",
          "Partially"
        ]
      },
      {
        "type": "checkbox",
        "question": "Operational constraints",
        "options": [
          "After-hours work",
          "Noise restrictions",
          "Dust containment",
          "Security clearance",
          "Customer access",
          "Loading / deliveries",
          "IT / equipment protection",
          "Other"
        ]
      },
      {
        "type": "radio",
        "question": "Are written safety / vendor requirements available?",
        "options": [
          "Yes",
          "No",
          "In progress"
        ]
      },
      {
        "type": "textarea",
        "question": "Describe required insurance, onboarding or compliance documents.",
        "options": []
      },
      {
        "type": "radio",
        "question": "Is phased construction required?",
        "options": [
          "Yes",
          "No",
          "To be determined"
        ]
      },
      {
        "type": "textarea",
        "question": "Describe critical milestones and blackout dates.",
        "options": []
      },
      {
        "type": "textarea",
        "question": "What reporting and communication cadence do you expect?",
        "options": []
      }
    ]
  },
  {
    "title": "Project Closeout & Warranty Readiness",
    "audience": "Project managers / customers",
    "description": "Confirm documentation, final approvals, manuals and warranty handoff.",
    "questions": [
      {
        "type": "radio",
        "question": "Are all punch-list items closed?",
        "options": [
          "Yes",
          "No",
          "Not applicable"
        ]
      },
      {
        "type": "radio",
        "question": "Are required inspections / approvals complete where applicable?",
        "options": [
          "Yes",
          "No",
          "Pending",
          "Not applicable"
        ]
      },
      {
        "type": "checkbox",
        "question": "Which closeout documents were delivered?",
        "options": [
          "Final invoice",
          "Warranty information",
          "Product manuals",
          "Care instructions",
          "Photos",
          "Approved change orders",
          "As-built documents where applicable"
        ]
      },
      {
        "type": "radio",
        "question": "Has the customer received maintenance guidance?",
        "options": [
          "Yes",
          "No",
          "Not applicable"
        ]
      },
      {
        "type": "radio",
        "question": "Is the final payment status documented?",
        "options": [
          "Yes",
          "No",
          "Pending"
        ]
      },
      {
        "type": "textarea",
        "question": "List outstanding documents or closeout actions.",
        "options": []
      },
      {
        "type": "input",
        "question": "Person responsible for remaining action",
        "options": []
      },
      {
        "type": "input",
        "question": "Target closeout date",
        "options": []
      },
      {
        "type": "textarea",
        "question": "Lessons learned for future projects.",
        "options": []
      }
    ]
  }
]
JSON
, true);
