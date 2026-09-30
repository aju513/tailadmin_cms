<div class="homepage__traininglist hav-title-btn bg-dim_bg common-box">
    <div class="container-fluid">
        <div class="container max-md:!px-0">
            <div class="flex items-center justify-between mb-4">
                <div class="section-title">Ongoing Trainings</div>
                <div class="section-title-btn">
                    <a href="https://tmis.pcgg.lumbini.gov.np/routines?status=all" target="_blank" rel="noopener noreferrer" class="btn-outline-secondary hav-icon px-4 py-1.5 rounded-lg group">
                        View All Trainings
                        <span
                            class="inline-block ml-1 text-base transition-transform duration-500 ease-in-out icon-arrow-up-right group-hover:translate-x-1"></span>
                    </a>
                </div>
            </div>
            <div class="training-list">
@for($trainingRepeat = 0; $trainingRepeat < 6; $trainingRepeat++)
                <article class="training-list__item">
                    <div class="training-list__badges">
                        <span class="training-list__badge training-list__badge--type">Leadership</span>
                        <span class="training-list__badge training-list__badge--status">Upcoming</span>
                    </div>
                    <h3 class="training-list__title">
                        <a href="https://tmis.pcgg.lumbini.gov.np/routines?status=all">प्रदेश तथा स्थानीय तहका छैठौं तहका अधिकृतस्तरका कर्मचारीहरुका लागि
                            सेवाकालिन प्रशिक्षण</a>
                    </h3>
                    <div class="training-list__meta">
                        <div class="training-list__meta-item">
                            <span class="icon-calendar-lines" aria-hidden="true"></span>
                            <span>18 Nov, 2026</span>
                        </div>
                        <div class="training-list__meta-item">
                            <span class="icon-location" aria-hidden="true"></span>
                            <span>Lumbini Research and Training Academy</span>
                        </div>
                    </div>
                    <a href="https://tmis.pcgg.lumbini.gov.np/routines?status=all" class="btn-primary hav-icon training-list__link">
                        <span>View Details</span>
                        <span class="btn-primary__icon icon-arrow-up-right" aria-hidden="true"></span>
                    </a>
                </article>
@endfor
            </div>
        </div>
    </div>
</div>
